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
                Section::make(__('filament.resource.hero_banners.banner_settings'))
                    ->schema([
                        TextInput::make('image')
                            ->label(__('filament.common.image_url'))
                            ->required()
                            ->maxLength(2048),
                        TextInput::make('cta_url')
                            ->label(__('filament.resource.hero_banners.cta_url'))
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('sort_order')
                            ->label(__('filament.common.sort_order'))
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
                                TextInput::make('title_ms')
                                    ->label(__('filament.common.title_bm'))
                                    ->maxLength(500),
                                TextInput::make('subtitle_ms')
                                    ->label(__('filament.resource.hero_banners.subtitle_bm'))
                                    ->maxLength(1000),
                                TextInput::make('image_alt_ms')
                                    ->label(__('filament.resource.hero_banners.image_alt_bm'))
                                    ->maxLength(500),
                                TextInput::make('cta_label_ms')
                                    ->label(__('filament.resource.hero_banners.cta_label_bm'))
                                    ->maxLength(200),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('title_en')
                                    ->label(__('filament.common.title_en'))
                                    ->maxLength(500),
                                TextInput::make('subtitle_en')
                                    ->label(__('filament.resource.hero_banners.subtitle_en'))
                                    ->maxLength(1000),
                                TextInput::make('image_alt_en')
                                    ->label(__('filament.resource.hero_banners.image_alt_en'))
                                    ->maxLength(500),
                                TextInput::make('cta_label_en')
                                    ->label(__('filament.resource.hero_banners.cta_label_en'))
                                    ->maxLength(200),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
