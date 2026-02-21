<?php

namespace App\Filament\Resources\SearchOverrides;

use App\Filament\Resources\SearchOverrides\Pages\CreateSearchOverride;
use App\Filament\Resources\SearchOverrides\Pages\EditSearchOverride;
use App\Filament\Resources\SearchOverrides\Pages\ListSearchOverrides;
use App\Filament\Resources\SearchOverrides\Schemas\SearchOverrideForm;
use App\Filament\Resources\SearchOverrides\Tables\SearchOverridesTable;
use App\Models\SearchOverride;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SearchOverrideResource extends Resource
{
    protected static ?string $model = SearchOverride::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.search_override', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.search_override', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return SearchOverrideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SearchOverridesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSearchOverrides::route('/'),
            'create' => CreateSearchOverride::route('/create'),
            'edit' => EditSearchOverride::route('/{record}/edit'),
        ];
    }
}
