<?php

namespace App\Filament\Resources\PageCategories\Pages;

use App\Filament\Resources\PageCategories\PageCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPageCategories extends ListRecords
{
    protected static string $resource = PageCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
