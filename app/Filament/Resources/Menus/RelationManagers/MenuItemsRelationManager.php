<?php

namespace App\Filament\Resources\Menus\RelationManagers;

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
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

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
            ->columns([
                TextColumn::make('label_ms')
                    ->label(__('filament.common.label_bm'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.label_ms')
                    ->label(__('filament.common.parent'))
                    ->placeholder(__('filament.common.root')),
                TextColumn::make('url')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament.common.active'))
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label(__('filament.resource.menu_items.system_item'))
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
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
}
