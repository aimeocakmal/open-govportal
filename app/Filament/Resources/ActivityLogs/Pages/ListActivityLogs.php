<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Services\ActivityLogPurgeService;
use App\Services\MediaDiskService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getArchiveAction(),
        ];
    }

    protected function getArchiveAction(): Action
    {
        $purgeService = app(ActivityLogPurgeService::class);
        $count = $purgeService->countEligible();
        $retentionDays = $purgeService->getRetentionDays();

        return Action::make('archive')
            ->label(__('filament.resource.activity_logs.archive_action'))
            ->icon(Heroicon::OutlinedArchiveBox)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(__('filament.resource.activity_logs.archive_confirm_heading'))
            ->modalDescription(__('filament.resource.activity_logs.archive_confirm_description', [
                'count' => $count,
                'days' => $retentionDays,
            ]))
            ->modalSubmitActionLabel(__('filament.resource.activity_logs.archive_confirm_button'))
            ->action(function (): void {
                $result = app(ActivityLogPurgeService::class)->archiveAndPurge(app(MediaDiskService::class));

                if ($result['archive'] === null) {
                    Notification::make()
                        ->info()
                        ->title(__('filament.resource.activity_logs.purge_nothing'))
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title(__('filament.resource.activity_logs.archive_success'))
                    ->body(__('filament.resource.activity_logs.purge_completed', [
                        'count' => $result['count'],
                        'archive' => $result['archive'],
                    ]))
                    ->persistent()
                    ->send();
            });
    }
}
