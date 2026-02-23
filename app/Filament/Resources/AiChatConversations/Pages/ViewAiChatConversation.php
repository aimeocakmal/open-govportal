<?php

namespace App\Filament\Resources\AiChatConversations\Pages;

use App\Filament\Resources\AiChatConversations\AiChatConversationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAiChatConversation extends ViewRecord
{
    protected static string $resource = AiChatConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
