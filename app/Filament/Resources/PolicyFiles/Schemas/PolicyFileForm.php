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
                Section::make('File Details')
                    ->schema([
                        TextInput::make('filename')
                            ->required()
                            ->maxLength(500),
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
                        Select::make('category')
                            ->options([
                                'pekeliling' => 'Pekeliling / Circular',
                                'garis_panduan' => 'Garis Panduan / Guidelines',
                                'laporan' => 'Laporan / Report',
                                'borang' => 'Borang / Form',
                            ])
                            ->nullable(),
                        Toggle::make('is_public')
                            ->label('Public')
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
