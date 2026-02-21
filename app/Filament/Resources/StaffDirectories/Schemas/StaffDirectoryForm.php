<?php

namespace App\Filament\Resources\StaffDirectories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class StaffDirectoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.common.general'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament.resource.staff_directories.name_label'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('phone')
                            ->label(__('filament.common.phone'))
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('fax')
                            ->label(__('filament.common.fax'))
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('photo')
                            ->label(__('filament.common.photo_url'))
                            ->maxLength(2048)
                            ->url()
                            ->nullable(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('filament.common.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Tabs::make(__('filament.common.content'))
                    ->tabs([
                        Tab::make(__('filament.common.bahasa_malaysia'))
                            ->schema([
                                TextInput::make('position_ms')
                                    ->label(__('filament.resource.staff_directories.position_bm'))
                                    ->maxLength(500),
                                TextInput::make('department_ms')
                                    ->label(__('filament.resource.staff_directories.department_bm'))
                                    ->maxLength(255),
                                TextInput::make('division_ms')
                                    ->label(__('filament.resource.staff_directories.division_bm'))
                                    ->maxLength(255),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('position_en')
                                    ->label(__('filament.resource.staff_directories.position_en'))
                                    ->maxLength(500),
                                TextInput::make('department_en')
                                    ->label(__('filament.resource.staff_directories.department_en'))
                                    ->maxLength(255),
                                TextInput::make('division_en')
                                    ->label(__('filament.resource.staff_directories.division_en'))
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
