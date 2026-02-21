<?php

namespace App\Filament\Resources\Broadcasts\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class BroadcastsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_ms')
                    ->label('Tajuk (BM)')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'announcement' => 'info',
                        'press_release' => 'success',
                        'news' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'announcement' => 'Pengumuman',
                        'press_release' => 'Siaran Akhbar',
                        'news' => 'Berita',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Diterbitkan',
                    ]),
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
