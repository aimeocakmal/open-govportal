<?php

namespace App\Filament\Resources\SearchOverrides\Pages;

use App\Filament\Resources\SearchOverrides\SearchOverrideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSearchOverride extends EditRecord
{
    protected static string $resource = SearchOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
