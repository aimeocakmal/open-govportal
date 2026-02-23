<?php

namespace App\Filament\Resources\AiChatConversations\Pages;

use App\Filament\Resources\AiChatConversations\AiChatConversationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAiChatConversation extends EditRecord
{
    protected static string $resource = AiChatConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
