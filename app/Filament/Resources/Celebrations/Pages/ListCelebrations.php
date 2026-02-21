<?php

namespace App\Filament\Resources\Celebrations\Pages;

use App\Filament\Resources\Celebrations\CelebrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCelebrations extends ListRecords
{
    protected static string $resource = CelebrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
