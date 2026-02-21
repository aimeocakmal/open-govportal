<?php

namespace App\Filament\Resources\Policies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PolicyForm
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
                            ->unique(ignoreRecord: true),
                        Select::make('category')
                            ->options([
                                'keselamatan' => 'Keselamatan / Security',
                                'data' => 'Data',
                                'digital' => 'Digital',
                                'ict' => 'ICT',
                                'perkhidmatan' => 'Perkhidmatan / Services',
                            ])
                            ->nullable(),
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
                        TextInput::make('file_url')
                            ->label('File URL (PDF)')
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->numeric()
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
                                Textarea::make('description_ms')
                                    ->label('Keterangan (BM)')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('title_en')
                                    ->label('Title (EN)')
                                    ->maxLength(500),
                                Textarea::make('description_en')
                                    ->label('Description (EN)')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
