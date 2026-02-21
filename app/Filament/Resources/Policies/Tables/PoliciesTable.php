<?php

namespace App\Filament\Resources\Policies\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PoliciesTable
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
                TextColumn::make('category')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('download_count')
                    ->label('Downloads')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Diterbitkan',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'keselamatan' => 'Keselamatan',
                        'data' => 'Data',
                        'digital' => 'Digital',
                        'ict' => 'ICT',
                        'perkhidmatan' => 'Perkhidmatan',
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
