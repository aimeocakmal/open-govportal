<?php

namespace App\Console\Commands;

use App\Services\AiPurgeService;
use App\Services\MediaDiskService;
use Illuminate\Console\Command;

class PurgeOldChatConversations extends Command
{
    protected $signature = 'ai:purge-conversations {--dry-run : Show what would be archived and deleted without actually doing it}';

    protected $description = 'Archive and purge old AI chat conversations and usage logs based on the configured retention period.';

    public function handle(AiPurgeService $purgeService, MediaDiskService $mediaDiskService): int
    {
        if ($this->option('dry-run')) {
            $counts = $purgeService->countEligible();
            $retentionDays = $purgeService->getRetentionDays();
            $this->info("[Dry run] Would archive and delete {$counts['conversations']} conversations and {$counts['logs']} usage logs older than {$retentionDays} days.");

            return self::SUCCESS;
        }

        $result = $purgeService->archiveAndPurge($mediaDiskService);

        if ($result['archive'] === null) {
            $this->info(__('ai.purge_nothing'));

            return self::SUCCESS;
        }

        $this->info(__('ai.purge_completed', [
            'conversations' => $result['conversations'],
            'logs' => $result['logs'],
            'archive' => $result['archive'],
        ]));

        return self::SUCCESS;
    }
}
