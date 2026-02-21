<?php

namespace App\Filament\Resources\PageCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.common.general'))
                    ->schema([
                        Select::make('parent_id')
                            ->label(__('filament.resource.page_categories.parent_category'))
                            ->relationship('parent', 'name_ms')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(300)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('filament.resource.page_categories.slug_help')),
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
                                TextInput::make('name_ms')
                                    ->label(__('filament.common.name_bm'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, ?string $old) {
                                        if (blank($old)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                Textarea::make('description_ms')
                                    ->label(__('filament.common.description_bm'))
                                    ->rows(3),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('name_en')
                                    ->label(__('filament.common.name_en'))
                                    ->maxLength(255),
                                Textarea::make('description_en')
                                    ->label(__('filament.common.description_en'))
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
