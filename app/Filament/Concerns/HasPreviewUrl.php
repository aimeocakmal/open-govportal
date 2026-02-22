<?php

namespace App\Filament\Concerns;

use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\Celebration;
use App\Models\Policy;
use App\Models\StaticPage;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\URL;

trait HasPreviewUrl
{
    protected function getPreviewAction(): Action
    {
        return Action::make('preview')
            ->label(__('filament.actions.preview'))
            ->icon(Heroicon::OutlinedEye)
            ->color('info')
            ->url(fn () => $this->generatePreviewUrl(), shouldOpenInNewTab: true);
    }

    protected function generatePreviewUrl(): string
    {
        $record = $this->record;
        $modelClass = get_class($record);

        $shortName = match ($modelClass) {
            Broadcast::class => 'broadcast',
            Achievement::class => 'achievement',
            Celebration::class => 'celebration',
            Policy::class => 'policy',
            StaticPage::class => 'static-page',
            default => 'unknown',
        };

        return URL::temporarySignedRoute(
            'preview.show',
            now()->addHours(1),
            ['model' => $shortName, 'id' => $record->id]
        );
    }
}
