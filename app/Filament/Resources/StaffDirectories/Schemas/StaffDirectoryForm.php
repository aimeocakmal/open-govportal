<?php

namespace App\Filament\Resources\StaffDirectories\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StaffDirectoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama / Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Telefon / Phone')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('fax')
                            ->label('Faks / Fax')
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('photo')
                            ->label('Photo URL')
                            ->maxLength(2048)
                            ->url()
                            ->nullable(),
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
                                TextInput::make('position_ms')
                                    ->label('Jawatan (BM)')
                                    ->maxLength(500),
                                TextInput::make('department_ms')
                                    ->label('Jabatan (BM)')
                                    ->maxLength(255),
                                TextInput::make('division_ms')
                                    ->label('Bahagian (BM)')
                                    ->maxLength(255),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('position_en')
                                    ->label('Position (EN)')
                                    ->maxLength(500),
                                TextInput::make('department_en')
                                    ->label('Department (EN)')
                                    ->maxLength(255),
                                TextInput::make('division_en')
                                    ->label('Division (EN)')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
