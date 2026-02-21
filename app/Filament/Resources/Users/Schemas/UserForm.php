<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Profile')
                    ->schema([
                        TextInput::make('department')
                            ->maxLength(255),
                        FileUpload::make('avatar')
                            ->image()
                            ->avatar()
                            ->directory('avatars'),
                        Select::make('preferred_locale')
                            ->label('Language')
                            ->options([
                                'ms' => 'Bahasa Malaysia',
                                'en' => 'English',
                            ])
                            ->default('ms')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Roles')
                    ->schema([
                        CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->columns(3),
                    ]),
            ]);
    }
}
