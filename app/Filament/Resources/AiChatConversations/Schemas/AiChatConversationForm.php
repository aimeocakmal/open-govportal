<?php

namespace App\Filament\Resources\AiChatConversations\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiChatConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('ai.conversation_details'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('ai.conversation_title'))
                            ->maxLength(255),
                        TagsInput::make('tags')
                            ->label(__('ai.tags'))
                            ->placeholder(__('ai.edit_tags'))
                            ->suggestions([
                                'soalan-umum',
                                'dasar',
                                'perkhidmatan',
                                'teknikal',
                                'aduan',
                                'maklumat',
                                'cadangan',
                            ]),
                    ]),

                Section::make(__('ai.conversation_details'))
                    ->schema([
                        Placeholder::make('ip_address')
                            ->label(__('ai.ip_address'))
                            ->content(fn ($record) => $record->ip_address ?? '—'),
                        Placeholder::make('locale')
                            ->label(__('ai.locale'))
                            ->content(fn ($record) => $record->locale ?? '—'),
                        Placeholder::make('message_count')
                            ->label(__('ai.message_count'))
                            ->content(fn ($record) => $record->message_count),
                        Placeholder::make('total_prompt_tokens')
                            ->label(__('ai.prompt_tokens'))
                            ->content(fn ($record) => number_format($record->total_prompt_tokens)),
                        Placeholder::make('total_completion_tokens')
                            ->label(__('ai.completion_tokens'))
                            ->content(fn ($record) => number_format($record->total_completion_tokens)),
                        Placeholder::make('started_at')
                            ->label(__('ai.started_at'))
                            ->content(fn ($record) => $record->started_at?->format('d M Y H:i:s') ?? '—'),
                        Placeholder::make('last_message_at')
                            ->label(__('ai.last_message_at'))
                            ->content(fn ($record) => $record->last_message_at?->format('d M Y H:i:s') ?? '—'),
                    ])
                    ->columns(3),
            ]);
    }
}
