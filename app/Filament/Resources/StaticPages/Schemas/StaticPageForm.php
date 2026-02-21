<?php

namespace App\Filament\Resources\StaticPages\Schemas;

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
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(600)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier'),
                        Select::make('category_id')
                            ->label('Category')
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
                            ->label('Include in Sitemap')
                            ->default(true),
                    ])
                    ->columns(2),
                Tabs::make('Content')
                    ->tabs([
                        Tab::make('Bahasa Malaysia')
                            ->schema([
                                TextInput::make('title_ms')
                                    ->label('Tajuk (BM)')
                                    ->required()
                                    ->maxLength(500)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, ?string $old) {
                                        if (blank($old)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                RichEditor::make('content_ms')
                                    ->label('Kandungan (BM)')
                                    ->columnSpanFull(),
                                Textarea::make('excerpt_ms')
                                    ->label('Ringkasan (BM)')
                                    ->maxLength(1000)
                                    ->rows(3),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('title_en')
                                    ->label('Title (EN)')
                                    ->maxLength(500),
                                RichEditor::make('content_en')
                                    ->label('Content (EN)')
                                    ->columnSpanFull(),
                                Textarea::make('excerpt_en')
                                    ->label('Excerpt (EN)')
                                    ->maxLength(1000)
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title_ms')
                            ->label('Meta Title (BM)')
                            ->maxLength(255),
                        TextInput::make('meta_title_en')
                            ->label('Meta Title (EN)')
                            ->maxLength(255),
                        Textarea::make('meta_desc_ms')
                            ->label('Meta Description (BM)')
                            ->maxLength(500)
                            ->rows(2),
                        Textarea::make('meta_desc_en')
                            ->label('Meta Description (EN)')
                            ->maxLength(500)
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
