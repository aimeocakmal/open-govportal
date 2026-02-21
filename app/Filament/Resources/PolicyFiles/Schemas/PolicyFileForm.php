<?php

namespace App\Filament\Resources\PolicyFiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PolicyFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.common.file_details'))
                    ->schema([
                        TextInput::make('filename')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('file_url')
                            ->label(__('filament.common.file_url'))
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('mime_type')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('file_size')
                            ->label(__('filament.common.file_size'))
                            ->numeric()
                            ->nullable(),
                        Select::make('category')
                            ->options([
                                'pekeliling' => 'Pekeliling / Circular',
                                'garis_panduan' => 'Garis Panduan / Guidelines',
                                'laporan' => 'Laporan / Report',
                                'borang' => 'Borang / Form',
                            ])
                            ->nullable(),
                        Toggle::make('is_public')
                            ->label(__('filament.common.public'))
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
