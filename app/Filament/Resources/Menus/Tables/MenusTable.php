<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->badge()
                    ->sortable(),
                TextColumn::make('label_ms')
                    ->label(__('filament.common.label_bm'))
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label(__('filament.resource.menus.items'))
                    ->counts('items')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament.common.active'))
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
