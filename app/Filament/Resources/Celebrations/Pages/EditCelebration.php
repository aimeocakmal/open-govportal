<?php

namespace App\Filament\Resources\Celebrations\Pages;

use App\Filament\Resources\Celebrations\CelebrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCelebration extends EditRecord
{
    protected static string $resource = CelebrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
