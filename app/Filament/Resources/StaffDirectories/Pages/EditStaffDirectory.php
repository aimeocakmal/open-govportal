<?php

namespace App\Filament\Resources\StaffDirectories\Pages;

use App\Filament\Resources\StaffDirectories\StaffDirectoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffDirectory extends EditRecord
{
    protected static string $resource = StaffDirectoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
