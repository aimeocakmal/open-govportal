<?php

namespace App\Filament\Resources\PageCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name_ms')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(300)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
                Tabs::make('Content')
                    ->tabs([
                        Tab::make('Bahasa Malaysia')
                            ->schema([
                                TextInput::make('name_ms')
                                    ->label('Nama (BM)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, ?string $old) {
                                        if (blank($old)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                Textarea::make('description_ms')
                                    ->label('Penerangan (BM)')
                                    ->rows(3),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('name_en')
                                    ->label('Name (EN)')
                                    ->maxLength(255),
                                Textarea::make('description_en')
                                    ->label('Description (EN)')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
