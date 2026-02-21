<?php

namespace App\Filament\Resources\Media\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('filename')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('original_name')
                    ->label(__('filament.resource.media.original'))
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('mime_type')
                    ->label(__('filament.resource.media.type'))
                    ->sortable(),
                TextColumn::make('file_size')
                    ->label(__('filament.common.size'))
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 0).' KB' : '—')
                    ->sortable(),
                TextColumn::make('width')
                    ->label(__('filament.resource.media.width'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('height')
                    ->label(__('filament.resource.media.height'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
