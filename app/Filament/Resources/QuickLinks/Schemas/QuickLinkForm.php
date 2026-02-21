<?php

namespace App\Filament\Resources\QuickLinks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuickLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.resource.quick_links.quick_link'))
                    ->schema([
                        TextInput::make('label_ms')
                            ->label(__('filament.common.label_bm'))
                            ->required()
                            ->maxLength(200),
                        TextInput::make('label_en')
                            ->label(__('filament.common.label_en'))
                            ->maxLength(200),
                        TextInput::make('url')
                            ->label(__('filament.common.url'))
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('icon')
                            ->label(__('filament.common.icon'))
                            ->maxLength(100)
                            ->helperText(__('filament.resource.quick_links.icon_help')),
                        TextInput::make('sort_order')
                            ->label(__('filament.common.sort_order'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('filament.common.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
