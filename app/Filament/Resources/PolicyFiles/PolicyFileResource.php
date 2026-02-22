<?php

namespace App\Filament\Resources\PolicyFiles;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\PolicyFiles\Pages\CreatePolicyFile;
use App\Filament\Resources\PolicyFiles\Pages\EditPolicyFile;
use App\Filament\Resources\PolicyFiles\Pages\ListPolicyFiles;
use App\Filament\Resources\PolicyFiles\Schemas\PolicyFileForm;
use App\Filament\Resources\PolicyFiles\Tables\PolicyFilesTable;
use App\Models\PolicyFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PolicyFileResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'policy-files';

    protected static ?string $model = PolicyFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperClip;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.policy_file', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.policy_file', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return PolicyFileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PolicyFilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPolicyFiles::route('/'),
            'create' => CreatePolicyFile::route('/create'),
            'edit' => EditPolicyFile::route('/{record}/edit'),
        ];
    }
}
