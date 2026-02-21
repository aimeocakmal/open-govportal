<?php

namespace App\Filament\Resources\StaticPages\Pages;

use App\Filament\Concerns\HasPublishActions;
use App\Filament\Resources\StaticPages\StaticPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStaticPage extends EditRecord
{
    use HasPublishActions;

    protected static string $resource = StaticPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPublishAction(),
            $this->getUnpublishAction(),
            DeleteAction::make(),
        ];
    }
}
