<?php

namespace App\Filament\Resources\SearchOverrides\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SearchOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.resource.search_overrides.search_rule'))
                    ->schema([
                        TextInput::make('query')
                            ->label(__('filament.resource.search_overrides.search_query'))
                            ->required()
                            ->maxLength(500)
                            ->helperText(__('filament.resource.search_overrides.search_query_help')),
                        TextInput::make('url')
                            ->label(__('filament.resource.search_overrides.target_url'))
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->helperText(__('filament.resource.search_overrides.priority_help')),
                        Toggle::make('is_active')
                            ->label(__('filament.common.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Tabs::make(__('filament.common.content'))
                    ->tabs([
                        Tab::make(__('filament.common.bahasa_malaysia'))
                            ->schema([
                                TextInput::make('title_ms')
                                    ->label(__('filament.common.title_bm'))
                                    ->maxLength(500),
                                Textarea::make('description_ms')
                                    ->label(__('filament.common.description_bm'))
                                    ->rows(3),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('title_en')
                                    ->label(__('filament.common.title_en'))
                                    ->maxLength(500),
                                Textarea::make('description_en')
                                    ->label(__('filament.common.description_en'))
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
