<?php

namespace App\Filament\Resources\StaticPages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\StaticPages\Pages\CreateStaticPage;
use App\Filament\Resources\StaticPages\Pages\EditStaticPage;
use App\Filament\Resources\StaticPages\Pages\ListStaticPages;
use App\Filament\Resources\StaticPages\Schemas\StaticPageForm;
use App\Filament\Resources\StaticPages\Tables\StaticPagesTable;
use App\Models\StaticPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaticPageResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'static-pages';

    protected static ?string $model = StaticPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.static_page', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.static_page', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return StaticPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaticPagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaticPages::route('/'),
            'create' => CreateStaticPage::route('/create'),
            'edit' => EditStaticPage::route('/{record}/edit'),
        ];
    }
}
