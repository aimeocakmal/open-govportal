<?php

namespace App\Filament\Resources\Policies;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\Policies\Pages\CreatePolicy;
use App\Filament\Resources\Policies\Pages\EditPolicy;
use App\Filament\Resources\Policies\Pages\ListPolicies;
use App\Filament\Resources\Policies\Schemas\PolicyForm;
use App\Filament\Resources\Policies\Tables\PoliciesTable;
use App\Models\Policy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PolicyResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'policies';

    protected static ?string $model = Policy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.policy', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.policy', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return PolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PoliciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPolicies::route('/'),
            'create' => CreatePolicy::route('/create'),
            'edit' => EditPolicy::route('/{record}/edit'),
        ];
    }
}
