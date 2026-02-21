<?php

namespace App\Filament\Resources\PageCategories\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PageCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ms')
                    ->label('Nama (BM)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name_ms')
                    ->label('Parent')
                    ->placeholder('Root'),
                TextColumn::make('static_pages_count')
                    ->label('Pages')
                    ->counts('staticPages')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
