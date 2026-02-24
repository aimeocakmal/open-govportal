<?php

namespace App\Filament\Resources\AiChatConversations\Pages;

use App\Filament\Resources\AiChatConversations\AiChatConversationResource;
use App\Services\AiPurgeService;
use App\Services\MediaDiskService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAiChatConversations extends ListRecords
{
    protected static string $resource = AiChatConversationResource::class;

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
        $purgeService = app(AiPurgeService::class);
        $counts = $purgeService->countEligible();
        $retentionDays = $purgeService->getRetentionDays();

        return Action::make('archive')
            ->label(__('ai.archive_action'))
            ->icon(Heroicon::OutlinedArchiveBox)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(__('ai.archive_confirm_heading'))
            ->modalDescription(__('ai.archive_confirm_description', [
                'conversations' => $counts['conversations'],
                'logs' => $counts['logs'],
                'days' => $retentionDays,
            ]))
            ->modalSubmitActionLabel(__('ai.archive_confirm_button'))
            ->action(function (): void {
                $result = app(AiPurgeService::class)->archiveAndPurge(app(MediaDiskService::class));

                if ($result['archive'] === null) {
                    Notification::make()
                        ->info()
                        ->title(__('ai.purge_nothing'))
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title(__('ai.archive_success'))
                    ->body(__('ai.purge_completed', [
                        'conversations' => $result['conversations'],
                        'logs' => $result['logs'],
                        'archive' => $result['archive'],
                    ]))
                    ->persistent()
                    ->send();
            });
    }
}
