<?php

namespace App\Filament\Resources\Feedback;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\Feedback\Pages\EditFeedback;
use App\Filament\Resources\Feedback\Pages\ListFeedback;
use App\Filament\Resources\Feedback\Schemas\FeedbackForm;
use App\Filament\Resources\Feedback\Tables\FeedbackTable;
use App\Models\Feedback;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'feedback';

    protected static ?string $model = Feedback::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.feedback', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.feedback', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return FeedbackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedback::route('/'),
            'edit' => EditFeedback::route('/{record}/edit'),
        ];
    }
}
