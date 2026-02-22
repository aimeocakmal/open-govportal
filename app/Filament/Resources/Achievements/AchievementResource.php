<?php

namespace App\Filament\Resources\Achievements;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\Achievements\Pages\CreateAchievement;
use App\Filament\Resources\Achievements\Pages\EditAchievement;
use App\Filament\Resources\Achievements\Pages\ListAchievements;
use App\Filament\Resources\Achievements\Schemas\AchievementForm;
use App\Filament\Resources\Achievements\Tables\AchievementsTable;
use App\Models\Achievement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AchievementResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'achievements';

    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.achievement', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.achievement', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return AchievementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AchievementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAchievements::route('/'),
            'create' => CreateAchievement::route('/create'),
            'edit' => EditAchievement::route('/{record}/edit'),
        ];
    }
}
