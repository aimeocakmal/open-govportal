<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?string $operation, $record) => $operation === 'edit' && in_array($record?->name, [
                                'super_admin', 'department_admin', 'content_editor',
                                'content_author', 'publisher', 'viewer',
                            ])),
                        TextInput::make('guard_name')
                            ->default('web')
                            ->disabled()
                            ->dehydrated(),
                    ]),
                Section::make('Permissions')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
            ]);
    }
}
