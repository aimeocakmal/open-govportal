<?php

namespace App\Jobs;

use App\Models\AiChatConversation;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateConversationTitleJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var list<int>
     */
    public array $backoff = [15, 30];

    public function __construct(public int $conversationId) {}

    public function handle(AiService $aiService): void
    {
        $conversation = AiChatConversation::find($this->conversationId);

        if (! $conversation || $conversation->title !== null) {
            return;
        }

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->limit(6)
            ->get(['role', 'content']);

        if ($messages->isEmpty()) {
            return;
        }

        $transcript = $messages->map(fn ($m) => mb_strtoupper($m->role).': '.mb_substr($m->content, 0, 300))
            ->implode("\n");

        $systemPrompt = 'You are a conversation classifier. Given a chat transcript, return a JSON object '
            .'with "title" (concise title, max 80 chars) and "tags" (array of 1-3 category tags from: '
            .'soalan-umum, dasar, perkhidmatan, teknikal, aduan, maklumat, cadangan). '
            .'Return ONLY valid JSON, no markdown fences or explanation.';

        if (! $aiService->isAvailable()) {
            return;
        }

        try {
            $response = $aiService->chat($transcript, [], $systemPrompt, $conversation->locale ?? 'ms');

            if ($response === '') {
                return;
            }

            $data = json_decode($response, true);

            if (is_array($data) && isset($data['title'])) {
                $conversation->update([
                    'title' => mb_substr($data['title'], 0, 255),
                    'tags' => $data['tags'] ?? null,
                ]);
            } else {
                // Fallback: use first user message as title
                $firstUserMessage = $messages->firstWhere('role', 'user');
                if ($firstUserMessage) {
                    $conversation->update([
                        'title' => mb_substr($firstUserMessage->content, 0, 255),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GenerateConversationTitleJob failed', [
                'conversation_id' => $this->conversationId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
