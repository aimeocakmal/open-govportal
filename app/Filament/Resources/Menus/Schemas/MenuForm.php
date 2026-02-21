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
                Section::make(__('filament.resource.menus.menu_details'))
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->maxLength(100),
                        TextInput::make('label_ms')
                            ->label(__('filament.common.label_bm'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('label_en')
                            ->label(__('filament.common.label_en'))
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label(__('filament.common.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
