<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use App\Models\MenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Spatie\Permission\Models\Role;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    /** @var array<int, string>|null */
    protected ?array $treePrefixCache = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('filament.common.content'))
                    ->tabs([
                        Tab::make(__('filament.common.bahasa_malaysia'))
                            ->schema([
                                TextInput::make('label_ms')
                                    ->label(__('filament.common.label_bm'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('label_en')
                                    ->label(__('filament.common.label_en'))
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament.resource.menu_items.navigation'))
                    ->schema([
                        TextInput::make('url')
                            ->label(__('filament.common.url'))
                            ->maxLength(2048)
                            ->helperText(__('filament.resource.menu_items.external_url_help')),
                        TextInput::make('route_name')
                            ->label(__('filament.resource.menu_items.route_name'))
                            ->maxLength(255)
                            ->helperText(__('filament.resource.menu_items.route_name_help')),
                        TextInput::make('icon')
                            ->maxLength(100)
                            ->helperText(__('filament.resource.menu_items.icon_help')),
                        Select::make('target')
                            ->options([
                                '_self' => __('filament.resource.menu_items.same_window'),
                                '_blank' => __('filament.resource.menu_items.new_window'),
                            ])
                            ->default('_self'),
                    ])
                    ->columns(2),
                Section::make(__('filament.resource.menu_items.hierarchy'))
                    ->schema([
                        Select::make('parent_id')
                            ->label(__('filament.resource.menu_items.parent_item'))
                            ->relationship('parent', 'label_ms', fn ($query) => $query->where('menu_id', $this->getOwnerRecord()->id))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        TextInput::make('mega_columns')
                            ->label(__('filament.resource.menu_items.mega_columns'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(4),
                        Toggle::make('is_active')
                            ->label(__('filament.common.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make(__('filament.resource.menu_items.access_control'))
                    ->schema([
                        CheckboxList::make('required_roles')
                            ->label(__('filament.resource.menu_items.visible_to_roles'))
                            ->options(fn () => Role::pluck('name', 'name')->toArray())
                            ->columns(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $orderedIds = $this->getTreeOrderedIds();

                if (! empty($orderedIds)) {
                    $cases = collect($orderedIds)
                        ->map(fn (int $id, int $index) => "WHEN {$id} THEN {$index}")
                        ->implode(' ');

                    $query->orderByRaw("CASE menu_items.id {$cases} ELSE 999999 END");
                }
            })
            ->columns([
                TextColumn::make('label_ms')
                    ->label(__('filament.common.label_bm'))
                    ->searchable()
                    ->formatStateUsing(function (string $state, MenuItem $record): string {
                        if ($record->parent_id === null) {
                            return $state;
                        }

                        $prefixes = $this->buildTreePrefixes();

                        return ($prefixes[$record->id] ?? '├── ').$state;
                    })
                    ->html(false),
                TextColumn::make('url')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('sort_order'),
                IconColumn::make('is_active')
                    ->label(__('filament.common.active'))
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label(__('filament.resource.menu_items.system_item'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn ($record) => $record->is_system),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records->reject(fn ($record) => $record->is_system)->each->delete();
                        }),
                ]),
            ]);
    }

    /**
     * Get item IDs in tree-walk order for SQL sorting.
     *
     * @return list<int>
     */
    protected function getTreeOrderedIds(): array
    {
        return array_keys($this->buildTreePrefixes());
    }

    /**
     * Build a prefix string for every menu item so the label column
     * renders with tree branch characters (├──, └──, │).
     *
     * @return array<int, string> Map of item ID → display prefix
     */
    protected function buildTreePrefixes(): array
    {
        if ($this->treePrefixCache !== null) {
            return $this->treePrefixCache;
        }

        $items = $this->getOwnerRecord()
            ->items()
            ->orderBy('sort_order')
            ->get();

        $this->treePrefixCache = [];
        $childrenByParent = $items->groupBy(
            fn (MenuItem $item) => $item->parent_id ?? 'root'
        );

        $this->walkTree($childrenByParent, 'root', '');

        return $this->treePrefixCache;
    }

    /**
     * Recursively walk the tree and populate treePrefixCache with
     * the correct branch characters for each item.
     */
    private function walkTree(BaseCollection $childrenByParent, string|int $parentKey, string $linePrefix): void
    {
        $siblings = $childrenByParent
            ->get($parentKey, collect())
            ->sortBy('sort_order')
            ->values();

        $lastIndex = $siblings->count() - 1;

        foreach ($siblings as $index => $item) {
            $isLast = $index === $lastIndex;

            if ($parentKey === 'root') {
                $this->treePrefixCache[$item->id] = '';
                $this->walkTree($childrenByParent, $item->id, '');
            } else {
                $this->treePrefixCache[$item->id] = $linePrefix.($isLast ? '└── ' : '├── ');
                $childLinePrefix = $linePrefix.($isLast ? '    ' : '│   ');
                $this->walkTree($childrenByParent, $item->id, $childLinePrefix);
            }
        }
    }
}
