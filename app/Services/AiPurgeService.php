<?php

namespace App\Services;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\AiUsageLog;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AiPurgeService
{
    /**
     * @return array{conversations: int, logs: int, archive: string|null}
     */
    public function archiveAndPurge(MediaDiskService $mediaDiskService): array
    {
        $retentionDays = $this->getRetentionDays();
        $cutoff = now()->subDays($retentionDays);

        $conversationQuery = $this->buildConversationQuery($cutoff);
        $logQuery = AiUsageLog::query()->where('created_at', '<', $cutoff);

        $conversationCount = (clone $conversationQuery)->count();
        $logCount = (clone $logQuery)->count();

        if ($conversationCount === 0 && $logCount === 0) {
            return ['conversations' => 0, 'logs' => 0, 'archive' => null];
        }

        $mediaDiskService->apply();
        $disk = $mediaDiskService->getActiveDiskName();

        $archivePath = $this->archiveRecords($conversationQuery, $logQuery, $disk);

        // Delete after successful archive
        $this->buildConversationQuery($cutoff)->delete();
        $logQuery->delete();

        return [
            'conversations' => $conversationCount,
            'logs' => $logCount,
            'archive' => $archivePath,
        ];
    }

    public function getRetentionDays(): int
    {
        $days = (int) Setting::get('ai_chat_retention_days', 90);

        return max($days, 30);
    }

    /**
     * @return array{conversations: int, logs: int}
     */
    public function countEligible(): array
    {
        $cutoff = now()->subDays($this->getRetentionDays());

        return [
            'conversations' => (clone $this->buildConversationQuery($cutoff))->count(),
            'logs' => AiUsageLog::query()->where('created_at', '<', $cutoff)->count(),
        ];
    }

    public function buildConversationQuery(\DateTimeInterface $cutoff): Builder
    {
        return AiChatConversation::query()
            ->where(function ($q) use ($cutoff) {
                $q->whereNotNull('last_message_at')->where('last_message_at', '<', $cutoff);
            })
            ->orWhere(function ($q) use ($cutoff) {
                $q->whereNull('last_message_at')->where('started_at', '<', $cutoff);
            });
    }

    private function archiveRecords(Builder $conversationQuery, Builder $logQuery, string $disk): string
    {
        $date = now()->format('Y-m-d');
        $timestamp = now()->format('His');
        $archiveDir = 'archives/ai-purge';
        $zipFilename = "ai-purge-{$date}-{$timestamp}.zip";

        $tempDir = storage_path("app/temp/ai-purge-{$date}-{$timestamp}");
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Export conversations with messages
        $conversationIds = (clone $conversationQuery)->pluck('id')->all();

        if (count($conversationIds) > 0) {
            $this->exportConversationsCsv($conversationIds, "{$tempDir}/conversations.csv");
            $this->exportMessagesCsv($conversationIds, "{$tempDir}/messages.csv");
        }

        // Export usage logs
        $logCount = (clone $logQuery)->count();

        if ($logCount > 0) {
            $this->exportUsageLogsCsv($logQuery, "{$tempDir}/usage_logs.csv");
        }

        // Create ZIP
        $zipPath = "{$tempDir}/{$zipFilename}";
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (glob("{$tempDir}/*.csv") as $csvFile) {
            $zip->addFile($csvFile, basename($csvFile));
        }

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

    /**
     * @param  array<int, int>  $conversationIds
     */
    private function exportConversationsCsv(array $conversationIds, string $path): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, [
            'id', 'session_id', 'ip_address', 'title', 'summary', 'tags',
            'locale', 'message_count', 'total_prompt_tokens', 'total_completion_tokens',
            'started_at', 'last_message_at', 'ended_at',
        ]);

        AiChatConversation::query()
            ->whereIn('id', $conversationIds)
            ->orderBy('id')
            ->chunk(500, function ($conversations) use ($handle) {
                foreach ($conversations as $c) {
                    fputcsv($handle, [
                        $c->id,
                        $c->session_id,
                        $c->ip_address,
                        $c->title,
                        $c->summary,
                        is_array($c->tags) ? json_encode($c->tags) : $c->tags,
                        $c->locale,
                        $c->message_count,
                        $c->total_prompt_tokens,
                        $c->total_completion_tokens,
                        $c->started_at?->toDateTimeString(),
                        $c->last_message_at?->toDateTimeString(),
                        $c->ended_at?->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);
    }

    /**
     * @param  array<int, int>  $conversationIds
     */
    private function exportMessagesCsv(array $conversationIds, string $path): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, [
            'id', 'conversation_id', 'role', 'content',
            'prompt_tokens', 'completion_tokens', 'duration_ms', 'created_at',
        ]);

        AiChatMessage::query()
            ->whereIn('conversation_id', $conversationIds)
            ->orderBy('id')
            ->chunk(500, function ($messages) use ($handle) {
                foreach ($messages as $m) {
                    fputcsv($handle, [
                        $m->id,
                        $m->conversation_id,
                        $m->role,
                        $m->content,
                        $m->prompt_tokens,
                        $m->completion_tokens,
                        $m->duration_ms,
                        $m->created_at?->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);
    }

    private function exportUsageLogsCsv(Builder $query, string $path): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, [
            'id', 'operation', 'source', 'locale', 'duration_ms',
            'prompt_tokens', 'completion_tokens', 'provider', 'model', 'created_at',
        ]);

        (clone $query)
            ->orderBy('id')
            ->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->operation,
                        $log->source,
                        $log->locale,
                        $log->duration_ms,
                        $log->prompt_tokens,
                        $log->completion_tokens,
                        $log->provider,
                        $log->model,
                        $log->created_at?->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);
    }
}
