<?php

namespace App\Filament\Resources\Media;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Media\Schemas\MediaForm;
use App\Filament\Resources\Media\Tables\MediaTable;
use App\Models\Media;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'media';

    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.media', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.media', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
