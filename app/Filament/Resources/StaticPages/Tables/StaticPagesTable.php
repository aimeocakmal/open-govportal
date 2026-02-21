<?php

namespace App\Filament\Resources\StaticPages\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class StaticPagesTable
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
                TextColumn::make('slug')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('category.name_ms')
                    ->label('Category')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
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
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'published'])),
                    BulkAction::make('unpublish')
                        ->color('warning')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'draft'])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
