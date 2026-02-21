<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.resource.feedback.submission_details'))
                    ->schema([
                        Placeholder::make('name')
                            ->content(fn ($record): string => $record?->name ?? '—'),
                        Placeholder::make('email')
                            ->content(fn ($record): string => $record?->email ?? '—'),
                        Placeholder::make('subject')
                            ->content(fn ($record): string => $record?->subject ?? '—'),
                        Placeholder::make('message_content')
                            ->label(__('filament.resource.feedback.message'))
                            ->content(fn ($record): string => $record?->message ?? '—'),
                        Placeholder::make('page_url')
                            ->label(__('filament.resource.feedback.page_url'))
                            ->content(fn ($record): string => $record?->page_url ?? '—'),
                        Placeholder::make('rating_display')
                            ->label(__('filament.resource.feedback.rating'))
                            ->content(fn ($record): string => $record?->rating ? $record->rating.'/5' : '—'),
                        Placeholder::make('ip_address')
                            ->label(__('filament.resource.feedback.ip_address'))
                            ->content(fn ($record): string => $record?->ip_address ?? '—'),
                        Placeholder::make('submitted_at')
                            ->label(__('filament.resource.feedback.submitted'))
                            ->content(fn ($record): string => $record?->created_at?->format('d M Y H:i') ?? '—'),
                    ])
                    ->columns(2),
                Section::make(__('filament.resource.feedback.admin_response'))
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'Baru / New',
                                'read' => 'Dibaca / Read',
                                'replied' => 'Dijawab / Replied',
                                'archived' => 'Diarkib / Archived',
                            ])
                            ->required(),
                        Textarea::make('reply')
                            ->label(__('filament.resource.feedback.reply'))
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
