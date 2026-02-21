<?php

namespace App\Filament\Resources\StaffDirectories\Pages;

use App\Filament\Resources\StaffDirectories\StaffDirectoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffDirectories extends ListRecords
{
    protected static string $resource = StaffDirectoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
