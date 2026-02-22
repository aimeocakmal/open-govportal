<?php

namespace App\Filament\Resources\HeroBanners;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\HeroBanners\Pages\CreateHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\EditHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\ListHeroBanners;
use App\Filament\Resources\HeroBanners\Schemas\HeroBannerForm;
use App\Filament\Resources\HeroBanners\Tables\HeroBannersTable;
use App\Models\HeroBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HeroBannerResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'hero-banners';

    protected static ?string $model = HeroBanner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.homepage');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.hero_banner', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.hero_banner', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return HeroBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HeroBannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHeroBanners::route('/'),
            'create' => CreateHeroBanner::route('/create'),
            'edit' => EditHeroBanner::route('/{record}/edit'),
        ];
    }
}
