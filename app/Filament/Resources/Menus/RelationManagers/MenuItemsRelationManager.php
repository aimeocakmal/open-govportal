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
use Spatie\Permission\Models\Role;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Content')
                    ->tabs([
                        Tab::make('Bahasa Malaysia')
                            ->schema([
                                TextInput::make('label_ms')
                                    ->label('Label (BM)')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('label_en')
                                    ->label('Label (EN)')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Navigation')
                    ->schema([
                        TextInput::make('url')
                            ->label('URL')
                            ->maxLength(2048)
                            ->helperText('External or absolute URL'),
                        TextInput::make('route_name')
                            ->label('Route Name')
                            ->maxLength(255)
                            ->helperText('Named Laravel route (alternative to URL)'),
                        TextInput::make('icon')
                            ->maxLength(100)
                            ->helperText('Heroicon name'),
                        Select::make('target')
                            ->options([
                                '_self' => 'Same Window',
                                '_blank' => 'New Window',
                            ])
                            ->default('_self'),
                    ])
                    ->columns(2),
                Section::make('Hierarchy & Display')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Item')
                            ->relationship('parent', 'label_ms', fn ($query) => $query->where('menu_id', $this->getOwnerRecord()->id))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        TextInput::make('mega_columns')
                            ->label('Mega Menu Columns')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(4),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Access Control')
                    ->schema([
                        CheckboxList::make('required_roles')
                            ->label('Visible to Roles (leave empty for all)')
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
                    ->label('Label (BM)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.label_ms')
                    ->label('Parent')
                    ->placeholder('Root'),
                TextColumn::make('url')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
