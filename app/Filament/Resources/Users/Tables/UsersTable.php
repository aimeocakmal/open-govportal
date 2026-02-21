<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.common.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('filament.common.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label(__('filament.common.roles'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('department')
                    ->label(__('filament.common.department'))
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('filament.common.active'))
                    ->boolean(),
                TextColumn::make('last_login_at')
                    ->label(__('filament.resource.users.last_login'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder(__('filament.resource.users.never')),
                TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament.resource.users.active_status')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('filament.actions.activate'))
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label(__('filament.actions.deactivate'))
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
