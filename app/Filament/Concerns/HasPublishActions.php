<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

trait HasPublishActions
{
    protected function getPublishAction(): Action
    {
        return Action::make('publish')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->requiresConfirmation()
            ->visible(fn () => $this->record->status !== 'published')
            ->action(function () {
                $this->record->update([
                    'status' => 'published',
                    'published_at' => $this->record->published_at ?? now(),
                ]);
                $this->refreshFormData(['status', 'published_at']);
                Notification::make()->success()->title('Published successfully')->send();
            });
    }

    protected function getUnpublishAction(): Action
    {
        return Action::make('unpublish')
            ->color('warning')
            ->icon('heroicon-o-x-circle')
            ->requiresConfirmation()
            ->visible(fn () => $this->record->status === 'published')
            ->action(function () {
                $this->record->update(['status' => 'draft']);
                $this->refreshFormData(['status']);
                Notification::make()->success()->title('Unpublished successfully')->send();
            });
    }
}
