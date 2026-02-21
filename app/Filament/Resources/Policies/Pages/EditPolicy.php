<?php

namespace App\Filament\Resources\Policies\Pages;

use App\Filament\Concerns\HasPublishActions;
use App\Filament\Resources\Policies\PolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPolicy extends EditRecord
{
    use HasPublishActions;

    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPublishAction(),
            $this->getUnpublishAction(),
            DeleteAction::make(),
        ];
    }
}
