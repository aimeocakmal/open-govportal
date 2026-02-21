<?php

namespace App\Filament\Resources\Celebrations\Pages;

use App\Filament\Concerns\HasPublishActions;
use App\Filament\Resources\Celebrations\CelebrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCelebration extends EditRecord
{
    use HasPublishActions;

    protected static string $resource = CelebrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPublishAction(),
            $this->getUnpublishAction(),
            DeleteAction::make(),
        ];
    }
}
