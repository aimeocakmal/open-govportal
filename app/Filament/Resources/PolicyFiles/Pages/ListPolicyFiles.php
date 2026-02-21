<?php

namespace App\Filament\Resources\PolicyFiles\Pages;

use App\Filament\Resources\PolicyFiles\PolicyFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPolicyFiles extends ListRecords
{
    protected static string $resource = PolicyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
