<?php

namespace App\Filament\Resources\SearchOverrides\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SearchOverridesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('query')
                    ->label(__('filament.resource.search_overrides.search_query'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title_ms')
                    ->label(__('filament.common.title_bm'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('url')
                    ->label(__('filament.common.url'))
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('priority')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament.common.active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('priority', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament.common.active')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
