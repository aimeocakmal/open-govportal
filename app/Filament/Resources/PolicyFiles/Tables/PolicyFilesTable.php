<?php

namespace App\Filament\Resources\PolicyFiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PolicyFilesTable
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
                TextColumn::make('filename')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pekeliling' => 'info',
                        'garis_panduan' => 'success',
                        'laporan' => 'warning',
                        'borang' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 0).' KB' : '—')
                    ->sortable(),
                TextColumn::make('download_count')
                    ->label('Downloads')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'pekeliling' => 'Pekeliling',
                        'garis_panduan' => 'Garis Panduan',
                        'laporan' => 'Laporan',
                        'borang' => 'Borang',
                    ]),
                TernaryFilter::make('is_public')
                    ->label('Public'),
            ])
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
