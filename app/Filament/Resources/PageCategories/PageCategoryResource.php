<?php

namespace App\Filament\Resources\PageCategories;

use App\Filament\Resources\PageCategories\Pages\CreatePageCategory;
use App\Filament\Resources\PageCategories\Pages\EditPageCategory;
use App\Filament\Resources\PageCategories\Pages\ListPageCategories;
use App\Filament\Resources\PageCategories\Schemas\PageCategoryForm;
use App\Filament\Resources\PageCategories\Tables\PageCategoriesTable;
use App\Models\PageCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PageCategoryResource extends Resource
{
    protected static ?string $model = PageCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PageCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPageCategories::route('/'),
            'create' => CreatePageCategory::route('/create'),
            'edit' => EditPageCategory::route('/{record}/edit'),
        ];
    }
}
