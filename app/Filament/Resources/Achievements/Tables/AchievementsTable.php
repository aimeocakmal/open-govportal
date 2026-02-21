<?php

namespace App\Filament\Resources\Achievements\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class AchievementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_ms')
                    ->label(__('filament.common.title_bm'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('filament.common.featured'))
                    ->boolean(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => __('filament.common.draft'),
                        'published' => __('filament.common.published'),
                    ]),
                TernaryFilter::make('is_featured')
                    ->label(__('filament.common.featured')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ])),
                    BulkAction::make('unpublish')
                        ->color('warning')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update([
                            'status' => 'draft',
                        ])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
