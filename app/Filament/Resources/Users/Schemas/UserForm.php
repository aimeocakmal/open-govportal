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
                Section::make(__('filament.resource.users.account'))
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
                Section::make(__('filament.resource.users.profile'))
                    ->schema([
                        TextInput::make('department')
                            ->maxLength(255),
                        FileUpload::make('avatar')
                            ->image()
                            ->avatar()
                            ->directory('avatars')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(1024)
                            ->placeholder(__('filament.common.file_upload_placeholder'))
                            ->helperText(__('filament.common.upload_help_avatar', ['size' => '1 MB'])),
                        Select::make('preferred_locale')
                            ->label(__('filament.resource.users.language'))
                            ->options([
                                'ms' => __('filament.common.bahasa_malaysia'),
                                'en' => __('filament.common.english'),
                            ])
                            ->default('ms')
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('filament.common.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make(__('filament.common.roles'))
                    ->schema([
                        CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->columns(3),
                    ]),
            ]);
    }
}
