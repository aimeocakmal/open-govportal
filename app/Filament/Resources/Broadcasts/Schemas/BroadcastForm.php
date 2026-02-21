<?php

namespace App\Filament\Resources\Broadcasts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BroadcastForm
{
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
                            ->helperText(__('filament.resource.broadcasts.slug_help')),
                        Select::make('type')
                            ->options([
                                'announcement' => 'Pengumuman / Announcement',
                                'press_release' => 'Siaran Akhbar / Press Release',
                                'news' => 'Berita / News',
                            ])
                            ->default('announcement')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draf / Draft',
                                'published' => 'Diterbitkan / Published',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label(__('filament.common.publish_date'))
                            ->nullable(),
                        TextInput::make('featured_image')
                            ->label(__('filament.resource.broadcasts.featured_image'))
                            ->maxLength(2048)
                            ->url()
                            ->nullable(),
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
                                    ->columnSpanFull(),
                                Textarea::make('excerpt_ms')
                                    ->label(__('filament.common.excerpt_bm'))
                                    ->maxLength(1000)
                                    ->rows(3),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('title_en')
                                    ->label(__('filament.common.title_en'))
                                    ->maxLength(500),
                                RichEditor::make('content_en')
                                    ->label(__('filament.common.content_en'))
                                    ->columnSpanFull(),
                                Textarea::make('excerpt_en')
                                    ->label(__('filament.common.excerpt_en'))
                                    ->maxLength(1000)
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
