<?php

namespace App\Console\Commands;

use App\Services\ActivityLogPurgeService;
use App\Services\MediaDiskService;
use Illuminate\Console\Command;

class PurgeOldActivityLogs extends Command
{
    protected $signature = 'activity-log:purge {--dry-run : Show what would be archived and deleted without actually doing it}';

    protected $description = 'Archive and purge old activity logs based on the configured retention period.';

    public function handle(ActivityLogPurgeService $purgeService, MediaDiskService $mediaDiskService): int
    {
        if ($this->option('dry-run')) {
            $count = $purgeService->countEligible();
            $retentionDays = $purgeService->getRetentionDays();
            $this->info("[Dry run] Would archive and delete {$count} activity logs older than {$retentionDays} days.");

            return self::SUCCESS;
        }

        $result = $purgeService->archiveAndPurge($mediaDiskService);

        if ($result['archive'] === null) {
            $this->info(__('filament.resource.activity_logs.purge_nothing'));

            return self::SUCCESS;
        }

        $this->info(__('filament.resource.activity_logs.purge_completed', [
            'count' => $result['count'],
            'archive' => $result['archive'],
        ]));

        return self::SUCCESS;
    }
}
