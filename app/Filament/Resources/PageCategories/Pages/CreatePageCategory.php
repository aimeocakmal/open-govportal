<?php

namespace App\Filament\Resources\PageCategories\Pages;

use App\Filament\Resources\PageCategories\PageCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePageCategory extends CreateRecord
{
    protected static string $resource = PageCategoryResource::class;
}
