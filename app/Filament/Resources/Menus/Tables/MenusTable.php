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
                    ->label('Label (BM)')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
