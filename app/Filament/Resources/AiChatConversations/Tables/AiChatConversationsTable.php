<?php

namespace App\Filament\Resources\AiChatConversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AiChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('started_at')
                    ->label(__('ai.started_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('ai.conversation_title'))
                    ->searchable()
                    ->placeholder(__('ai.untitled_conversation'))
                    ->limit(50),
                TextColumn::make('ip_address')
                    ->label(__('ai.ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('locale')
                    ->label(__('ai.locale'))
                    ->badge(),
                TextColumn::make('message_count')
                    ->label(__('ai.message_count'))
                    ->sortable(),
                TextColumn::make('total_tokens')
                    ->label(__('ai.total_tokens'))
                    ->getStateUsing(fn ($record) => number_format($record->total_prompt_tokens + $record->total_completion_tokens))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByRaw("(total_prompt_tokens + total_completion_tokens) {$direction}")),
                TextColumn::make('tags')
                    ->label(__('ai.tags'))
                    ->badge()
                    ->placeholder(__('ai.no_tags')),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                SelectFilter::make('locale')
                    ->options([
                        'ms' => 'BM',
                        'en' => 'EN',
                    ]),
                Filter::make('has_tags')
                    ->label(__('ai.has_tags'))
                    ->query(fn (Builder $query) => $query->whereNotNull('tags')),
                Filter::make('started_from')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('started_from')
                            ->label(__('ai.date_from')),
                        \Filament\Forms\Components\DatePicker::make('started_until')
                            ->label(__('ai.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['started_from'], fn (Builder $q, $date) => $q->whereDate('started_at', '>=', $date))
                            ->when($data['started_until'], fn (Builder $q, $date) => $q->whereDate('started_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
