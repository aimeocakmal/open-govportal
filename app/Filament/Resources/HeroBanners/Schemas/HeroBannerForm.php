<?php

namespace App\Filament\Resources\HeroBanners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class HeroBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner Settings')
                    ->schema([
                        TextInput::make('image')
                            ->label('Image URL')
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('cta_url')
                            ->label('CTA URL')
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
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
                                TextInput::make('title_ms')
                                    ->label('Tajuk (BM)')
                                    ->maxLength(500),
                                TextInput::make('subtitle_ms')
                                    ->label('Subtajuk (BM)')
                                    ->maxLength(1000),
                                TextInput::make('image_alt_ms')
                                    ->label('Image Alt (BM)')
                                    ->maxLength(500),
                                TextInput::make('cta_label_ms')
                                    ->label('CTA Label (BM)')
                                    ->maxLength(200),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('title_en')
                                    ->label('Title (EN)')
                                    ->maxLength(500),
                                TextInput::make('subtitle_en')
                                    ->label('Subtitle (EN)')
                                    ->maxLength(1000),
                                TextInput::make('image_alt_en')
                                    ->label('Image Alt (EN)')
                                    ->maxLength(500),
                                TextInput::make('cta_label_en')
                                    ->label('CTA Label (EN)')
                                    ->maxLength(200),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
