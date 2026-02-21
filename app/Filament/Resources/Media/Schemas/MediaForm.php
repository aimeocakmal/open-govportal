<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MediaForm
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
                        TextInput::make('original_name')
                            ->maxLength(500)
                            ->nullable(),
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
                        TextInput::make('width')
                            ->label(__('filament.resource.media.width_px'))
                            ->numeric()
                            ->nullable(),
                        TextInput::make('height')
                            ->label(__('filament.resource.media.height_px'))
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),
                Tabs::make(__('filament.common.content'))
                    ->tabs([
                        Tab::make(__('filament.common.bahasa_malaysia'))
                            ->schema([
                                TextInput::make('alt_ms')
                                    ->label(__('filament.resource.media.alt_bm'))
                                    ->maxLength(500),
                                TextInput::make('caption_ms')
                                    ->label(__('filament.common.caption_bm'))
                                    ->maxLength(1000),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('alt_en')
                                    ->label(__('filament.resource.media.alt_en'))
                                    ->maxLength(500),
                                TextInput::make('caption_en')
                                    ->label(__('filament.resource.media.caption_en'))
                                    ->maxLength(1000),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
