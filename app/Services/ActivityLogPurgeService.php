<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use ZipArchive;

class ActivityLogPurgeService
{
    /**
     * @return array{count: int, archive: string|null}
     */
    public function archiveAndPurge(MediaDiskService $mediaDiskService): array
    {
        $retentionDays = $this->getRetentionDays();
        $cutoff = now()->subDays($retentionDays);

        $query = $this->buildQuery($cutoff);
        $count = (clone $query)->count();

        if ($count === 0) {
            return ['count' => 0, 'archive' => null];
        }

        $mediaDiskService->apply();
        $disk = $mediaDiskService->getActiveDiskName();

        $archivePath = $this->archiveRecords($query, $disk);

        // Delete after successful archive
        $this->buildQuery($cutoff)->delete();

        return [
            'count' => $count,
            'archive' => $archivePath,
        ];
    }

    public function getRetentionDays(): int
    {
        $days = (int) Setting::get('activity_log_retention_days', 365);

        return max($days, 30);
    }

    public function countEligible(): int
    {
        $cutoff = now()->subDays($this->getRetentionDays());

        return $this->buildQuery($cutoff)->count();
    }

    private function buildQuery(\DateTimeInterface $cutoff): Builder
    {
        return Activity::query()->where('created_at', '<', $cutoff);
    }

    private function archiveRecords(Builder $query, string $disk): string
    {
        $date = now()->format('Y-m-d');
        $timestamp = now()->format('His');
        $archiveDir = 'archives/activity-log-purge';
        $zipFilename = "activity-log-purge-{$date}-{$timestamp}.zip";

        $tempDir = storage_path("app/temp/activity-log-purge-{$date}-{$timestamp}");
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $csvPath = "{$tempDir}/activity_log.csv";
        $this->exportCsv($query, $csvPath);

        // Create ZIP
        $zipPath = "{$tempDir}/{$zipFilename}";
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($csvPath, 'activity_log.csv');
        $zip->close();

        // Upload to configured media disk
        $storagePath = "{$archiveDir}/{$zipFilename}";
        Storage::disk($disk)->put($storagePath, file_get_contents($zipPath));

        // Clean up temp files
        foreach (glob("{$tempDir}/*") as $file) {
            unlink($file);
        }

        rmdir($tempDir);

        return $storagePath;
    }

    private function exportCsv(Builder $query, string $path): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, [
            'id', 'log_name', 'description', 'subject_type', 'subject_id',
            'causer_type', 'causer_id', 'properties', 'event', 'batch_uuid',
            'created_at', 'updated_at',
        ]);

        (clone $query)
            ->orderBy('id')
            ->chunk(500, function ($activities) use ($handle) {
                foreach ($activities as $activity) {
                    fputcsv($handle, [
                        $activity->id,
                        $activity->log_name,
                        $activity->description,
                        $activity->subject_type,
                        $activity->subject_id,
                        $activity->causer_type,
                        $activity->causer_id,
                        json_encode($activity->properties),
                        $activity->event,
                        $activity->batch_uuid,
                        $activity->created_at?->toDateTimeString(),
                        $activity->updated_at?->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);
    }
}
