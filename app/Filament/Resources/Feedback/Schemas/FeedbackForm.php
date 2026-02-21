<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission Details')
                    ->schema([
                        Placeholder::make('name')
                            ->content(fn ($record): string => $record?->name ?? '—'),
                        Placeholder::make('email')
                            ->content(fn ($record): string => $record?->email ?? '—'),
                        Placeholder::make('subject')
                            ->content(fn ($record): string => $record?->subject ?? '—'),
                        Placeholder::make('message_content')
                            ->label('Message')
                            ->content(fn ($record): string => $record?->message ?? '—'),
                        Placeholder::make('page_url')
                            ->label('Page URL')
                            ->content(fn ($record): string => $record?->page_url ?? '—'),
                        Placeholder::make('rating_display')
                            ->label('Rating')
                            ->content(fn ($record): string => $record?->rating ? $record->rating.'/5' : '—'),
                        Placeholder::make('ip_address')
                            ->label('IP Address')
                            ->content(fn ($record): string => $record?->ip_address ?? '—'),
                        Placeholder::make('submitted_at')
                            ->label('Submitted')
                            ->content(fn ($record): string => $record?->created_at?->format('d M Y H:i') ?? '—'),
                    ])
                    ->columns(2),
                Section::make('Admin Response')
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
                            ->label('Reply')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
