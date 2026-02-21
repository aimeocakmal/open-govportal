<?php

namespace App\Filament\Resources\StaffDirectories\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class StaffDirectoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama / Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_ms')
                    ->label('Jawatan (BM)')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('department_ms')
                    ->label('Jabatan (BM)')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
