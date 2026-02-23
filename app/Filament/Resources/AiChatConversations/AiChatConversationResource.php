<?php

namespace App\Filament\Resources\AiChatConversations;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\AiChatConversations\Pages\EditAiChatConversation;
use App\Filament\Resources\AiChatConversations\Pages\ListAiChatConversations;
use App\Filament\Resources\AiChatConversations\Pages\ViewAiChatConversation;
use App\Filament\Resources\AiChatConversations\Schemas\AiChatConversationForm;
use App\Filament\Resources\AiChatConversations\Tables\AiChatConversationsTable;
use App\Models\AiChatConversation;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AiChatConversationResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'ai-chat-conversations';

    protected static ?string $model = AiChatConversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 21;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.logs');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.ai_chat_conversation', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.ai_chat_conversation', 2);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_ai_settings') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AiChatConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiChatConversationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('ai.conversation_details'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('ai.conversation_title'))
                            ->placeholder(__('ai.untitled_conversation')),
                        TextEntry::make('ip_address')
                            ->label(__('ai.ip_address')),
                        TextEntry::make('locale')
                            ->label(__('ai.locale'))
                            ->badge(),
                        TextEntry::make('message_count')
                            ->label(__('ai.message_count')),
                        TextEntry::make('total_prompt_tokens')
                            ->label(__('ai.prompt_tokens')),
                        TextEntry::make('total_completion_tokens')
                            ->label(__('ai.completion_tokens')),
                        TextEntry::make('started_at')
                            ->label(__('ai.started_at'))
                            ->dateTime('d M Y H:i:s'),
                        TextEntry::make('last_message_at')
                            ->label(__('ai.last_message_at'))
                            ->dateTime('d M Y H:i:s')
                            ->placeholder('—'),
                        TextEntry::make('ended_at')
                            ->label(__('ai.ended_at'))
                            ->dateTime('d M Y H:i:s')
                            ->placeholder('—'),
                        TextEntry::make('tags')
                            ->label(__('ai.tags'))
                            ->badge()
                            ->placeholder(__('ai.no_tags')),
                    ])
                    ->columns(3),

                Section::make(__('ai.message_thread'))
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('messages')
                            ->view('filament.resources.ai-chat-conversations.message-thread')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiChatConversations::route('/'),
            'view' => ViewAiChatConversation::route('/{record}'),
            'edit' => EditAiChatConversation::route('/{record}/edit'),
        ];
    }
}
