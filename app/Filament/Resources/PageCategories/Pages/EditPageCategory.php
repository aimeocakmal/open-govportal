<?php

namespace App\Filament\Resources\PageCategories\Pages;

use App\Filament\Resources\PageCategories\PageCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPageCategory extends EditRecord
{
    protected static string $resource = PageCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
