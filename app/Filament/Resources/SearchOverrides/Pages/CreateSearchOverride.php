<?php

namespace App\Filament\Resources\SearchOverrides\Pages;

use App\Filament\Resources\SearchOverrides\SearchOverrideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSearchOverride extends CreateRecord
{
    protected static string $resource = SearchOverrideResource::class;
}
