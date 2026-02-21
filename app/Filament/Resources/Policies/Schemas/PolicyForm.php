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
                Section::make(__('filament.common.general'))
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
                            ->label(__('filament.common.publish_date'))
                            ->nullable(),
                        TextInput::make('file_url')
                            ->label(__('filament.resource.policies.file_url_pdf'))
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('file_size')
                            ->label(__('filament.common.file_size'))
                            ->numeric()
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
                                Textarea::make('description_ms')
                                    ->label(__('filament.common.description_bm'))
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make(__('filament.common.english'))
                            ->schema([
                                TextInput::make('title_en')
                                    ->label(__('filament.common.title_en'))
                                    ->maxLength(500),
                                Textarea::make('description_en')
                                    ->label(__('filament.common.description_en'))
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
