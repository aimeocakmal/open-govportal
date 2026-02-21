<?php

namespace App\Filament\Resources\Celebrations\Pages;

use App\Filament\Resources\Celebrations\CelebrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCelebration extends CreateRecord
{
    protected static string $resource = CelebrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
