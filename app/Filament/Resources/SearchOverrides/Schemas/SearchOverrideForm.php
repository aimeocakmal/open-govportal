<?php

namespace App\Filament\Resources\SearchOverrides\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SearchOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Search Rule')
                    ->schema([
                        TextInput::make('query')
                            ->label('Search Query')
                            ->required()
                            ->maxLength(500)
                            ->helperText('The keyword(s) this override matches.'),
                        TextInput::make('url')
                            ->label('Target URL')
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher priority = shown first.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
                Tabs::make('Content')
                    ->tabs([
                        Tab::make('Bahasa Malaysia')
                            ->schema([
                                TextInput::make('title_ms')
                                    ->label('Tajuk (BM)')
                                    ->maxLength(500),
                                Textarea::make('description_ms')
                                    ->label('Keterangan (BM)')
                                    ->rows(3),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('title_en')
                                    ->label('Title (EN)')
                                    ->maxLength(500),
                                Textarea::make('description_en')
                                    ->label('Description (EN)')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
