<?php

namespace App\Filament\Resources\PolicyFiles\Pages;

use App\Filament\Resources\PolicyFiles\PolicyFileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPolicyFile extends EditRecord
{
    protected static string $resource = PolicyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
