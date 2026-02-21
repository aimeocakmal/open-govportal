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
                Section::make('General')
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(600)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier. Auto-generated from BM title if left empty.'),
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
                            ->label('Publish Date')
                            ->nullable(),
                        TextInput::make('featured_image')
                            ->label('Featured Image URL')
                            ->maxLength(2048)
                            ->url()
                            ->nullable(),
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
            ]);
    }
}
