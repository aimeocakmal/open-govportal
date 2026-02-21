<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu Details')
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->maxLength(100),
                        TextInput::make('label_ms')
                            ->label('Label (BM)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('label_en')
                            ->label('Label (EN)')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
