<?php

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Concerns\HasPublishActions;
use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAchievement extends EditRecord
{
    use HasPublishActions;

    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPublishAction(),
            $this->getUnpublishAction(),
            DeleteAction::make(),
        ];
    }
}
