<?php

namespace App\Filament\Resources\Policies\Pages;

use App\Filament\Resources\Policies\PolicyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePolicy extends CreateRecord
{
    protected static string $resource = PolicyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
