<?php

namespace App\Filament\Resources\StaffDirectories;

use App\Filament\Resources\StaffDirectories\Pages\CreateStaffDirectory;
use App\Filament\Resources\StaffDirectories\Pages\EditStaffDirectory;
use App\Filament\Resources\StaffDirectories\Pages\ListStaffDirectories;
use App\Filament\Resources\StaffDirectories\Schemas\StaffDirectoryForm;
use App\Filament\Resources\StaffDirectories\Tables\StaffDirectoriesTable;
use App\Models\StaffDirectory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaffDirectoryResource extends Resource
{
    protected static ?string $model = StaffDirectory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return StaffDirectoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffDirectoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffDirectories::route('/'),
            'create' => CreateStaffDirectory::route('/create'),
            'edit' => EditStaffDirectory::route('/{record}/edit'),
        ];
    }
}
