<?php

namespace App\Filament\Resources\Celebrations;

use App\Filament\Resources\Celebrations\Pages\CreateCelebration;
use App\Filament\Resources\Celebrations\Pages\EditCelebration;
use App\Filament\Resources\Celebrations\Pages\ListCelebrations;
use App\Filament\Resources\Celebrations\Schemas\CelebrationForm;
use App\Filament\Resources\Celebrations\Tables\CelebrationsTable;
use App\Models\Celebration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CelebrationResource extends Resource
{
    protected static ?string $model = Celebration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.celebration', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.celebration', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return CelebrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CelebrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCelebrations::route('/'),
            'create' => CreateCelebration::route('/create'),
            'edit' => EditCelebration::route('/{record}/edit'),
        ];
    }
}
