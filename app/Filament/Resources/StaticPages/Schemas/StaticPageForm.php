<?php

namespace App\Filament\Resources\StaticPages\Schemas;

use App\Filament\Concerns\HasAiEditorActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StaticPageForm
{
    use HasAiEditorActions;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.common.general'))
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(600)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('filament.resource.static_pages.slug_help')),
                        Select::make('category_id')
                            ->label(__('filament.common.category'))
                            ->relationship('category', 'name_ms')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draf / Draft',
                                'published' => 'Diterbitkan / Published',
                            ])
                            ->default('draft')
                            ->required(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_in_sitemap')
                            ->label(__('filament.resource.static_pages.include_in_sitemap'))
                            ->default(true),
                    ])
                    ->columns(2),
                Tabs::make(__('filament.common.content'))
                    ->tabs([
                        Tab::make(__('filament.common.bahasa_malaysia'))
                            ->schema([
                                TextInput::make('title_ms')
                                    ->label(__('filament.common.title_bm'))
                                    ->required()
                                    ->maxLength(500)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, ?string $old) {
                                        if (blank($old)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                RichEditor::make('content_ms')
                                    ->label(__('filament.common.content_bm'))
                                    ->columnSpanFull()
                                    ->afterLabel(self::richEditorAiActions('ms', 'en', 'content_ms', 'content_en')),
                                Textarea::make('excerpt_ms')
                                    ->label(__('filament.common.excerpt_bm'))
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->afterLabel(self::excerptAiActions('ms', 'en', 'excerpt_ms', 'excerpt_en')),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('title_en')
                                    ->label(__('filament.common.title_en'))
                                    ->maxLength(500),
                                RichEditor::make('content_en')
                                    ->label(__('filament.common.content_en'))
                                    ->columnSpanFull()
                                    ->afterLabel(self::richEditorAiActions('en', 'ms', 'content_en', 'content_ms')),
                                Textarea::make('excerpt_en')
                                    ->label(__('filament.common.excerpt_en'))
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->afterLabel(self::excerptAiActions('en', 'ms', 'excerpt_en', 'excerpt_ms')),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament.resource.static_pages.seo'))
                    ->schema([
                        TextInput::make('meta_title_ms')
                            ->label(__('filament.resource.static_pages.meta_title_bm'))
                            ->maxLength(255),
                        TextInput::make('meta_title_en')
                            ->label(__('filament.resource.static_pages.meta_title_en'))
                            ->maxLength(255),
                        Textarea::make('meta_desc_ms')
                            ->label(__('filament.resource.static_pages.meta_desc_bm'))
                            ->maxLength(500)
                            ->rows(2),
                        Textarea::make('meta_desc_en')
                            ->label(__('filament.resource.static_pages.meta_desc_en'))
                            ->maxLength(500)
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
