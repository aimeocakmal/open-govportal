<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('filament.resource.activity_logs.timestamp'))
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label(__('filament.resource.activity_logs.user'))
                    ->placeholder(__('filament.resource.activity_logs.system'))
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label(__('filament.resource.activity_logs.module'))
                    ->formatStateUsing(fn (?string $state) => ActivityLogResource::getModuleName($state)),
                TextColumn::make('description')
                    ->label(__('filament.resource.activity_logs.event'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_id')
                    ->label(__('filament.resource.activity_logs.record_id'))
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('description')
                    ->label(__('filament.resource.activity_logs.event'))
                    ->options([
                        'created' => __('filament.resource.activity_logs.event_created'),
                        'updated' => __('filament.resource.activity_logs.event_updated'),
                        'deleted' => __('filament.resource.activity_logs.event_deleted'),
                    ]),
                SelectFilter::make('subject_type')
                    ->label(__('filament.resource.activity_logs.module'))
                    ->options(fn () => ActivityLogResource::getModuleOptions()),
                SelectFilter::make('causer_id')
                    ->label(__('filament.resource.activity_logs.user'))
                    ->options(fn () => User::pluck('name', 'id')->all()),
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label(__('filament.resource.activity_logs.date_from')),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label(__('filament.resource.activity_logs.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
