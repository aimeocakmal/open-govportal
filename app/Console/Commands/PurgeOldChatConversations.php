<?php

namespace App\Console\Commands;

use App\Models\AiChatConversation;
use App\Models\AiUsageLog;
use App\Models\Setting;
use Illuminate\Console\Command;

class PurgeOldChatConversations extends Command
{
    protected $signature = 'ai:purge-conversations {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Purge old AI chat conversations and usage logs based on the configured retention period.';

    public function handle(): int
    {
        $retentionDays = (int) Setting::get('ai_chat_retention_days', 90);

        if ($retentionDays < 7) {
            $retentionDays = 7;
        }

        $cutoff = now()->subDays($retentionDays);

        $conversationCount = AiChatConversation::query()
            ->where(function ($q) use ($cutoff) {
                $q->whereNotNull('last_message_at')->where('last_message_at', '<', $cutoff);
            })
            ->orWhere(function ($q) use ($cutoff) {
                $q->whereNull('last_message_at')->where('started_at', '<', $cutoff);
            })
            ->count();

        $logCount = AiUsageLog::query()
            ->where('created_at', '<', $cutoff)
            ->count();

        if ($this->option('dry-run')) {
            $this->info("[Dry run] Would delete {$conversationCount} conversations and {$logCount} usage logs older than {$retentionDays} days.");

            return self::SUCCESS;
        }

        AiChatConversation::query()
            ->where(function ($q) use ($cutoff) {
                $q->whereNotNull('last_message_at')->where('last_message_at', '<', $cutoff);
            })
            ->orWhere(function ($q) use ($cutoff) {
                $q->whereNull('last_message_at')->where('started_at', '<', $cutoff);
            })
            ->delete();

        AiUsageLog::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info(__('ai.purge_completed', [
            'conversations' => $conversationCount,
            'logs' => $logCount,
        ]));

        return self::SUCCESS;
    }
}
