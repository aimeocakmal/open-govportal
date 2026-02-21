<?php

namespace App\Filament\Resources\Broadcasts\Pages;

use App\Filament\Concerns\HasPublishActions;
use App\Filament\Resources\Broadcasts\BroadcastResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBroadcast extends EditRecord
{
    use HasPublishActions;

    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPublishAction(),
            $this->getUnpublishAction(),
            DeleteAction::make(),
        ];
    }
}
