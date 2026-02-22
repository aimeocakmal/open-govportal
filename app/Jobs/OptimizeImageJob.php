<?php

namespace App\Jobs;

use App\Services\ImageOptimizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OptimizeImageJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $path,
        public ?string $disk = null,
        public bool $createThumbnail = false,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImageOptimizationService $service): void
    {
        $optimizedPath = $service->optimize($this->path, $this->disk);

        if ($this->createThumbnail) {
            $service->createThumbnail($optimizedPath, disk: $this->disk);
        }
    }
}
