<?php

namespace App\Filament\Resources\QuickLinks\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuickLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quick Link')
                    ->schema([
                        TextInput::make('label_ms')
                            ->label('Label (BM)')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('label_en')
                            ->label('Label (EN)')
                            ->maxLength(200),
                        TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('icon')
                            ->label('Icon')
                            ->maxLength(100)
                            ->helperText('Heroicon name or icon identifier'),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
