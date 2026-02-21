<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('File Details')
                    ->schema([
                        TextInput::make('filename')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('original_name')
                            ->maxLength(500)
                            ->nullable(),
                        TextInput::make('file_url')
                            ->label('File URL / S3 Key')
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('mime_type')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('width')
                            ->label('Width (px)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('height')
                            ->label('Height (px)')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),
                Tabs::make('Content')
                    ->tabs([
                        Tab::make('Bahasa Malaysia')
                            ->schema([
                                TextInput::make('alt_ms')
                                    ->label('Alt Text (BM)')
                                    ->maxLength(500),
                                TextInput::make('caption_ms')
                                    ->label('Kapsyen (BM)')
                                    ->maxLength(1000),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('alt_en')
                                    ->label('Alt Text (EN)')
                                    ->maxLength(500),
                                TextInput::make('caption_en')
                                    ->label('Caption (EN)')
                                    ->maxLength(1000),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
