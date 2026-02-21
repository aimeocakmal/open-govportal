<?php

namespace App\Filament\Resources\SearchOverrides\Pages;

use App\Filament\Resources\SearchOverrides\SearchOverrideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSearchOverrides extends ListRecords
{
    protected static string $resource = SearchOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
