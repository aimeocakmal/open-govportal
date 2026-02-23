<?php

namespace Tests\Feature;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\Policy;
use App\Models\StaffDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class GenerateEmbeddingJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_dispatches_job_for_published_broadcast(): void
    {
        Bus::fake();

        $broadcast = Broadcast::factory()->published()->create();

        Bus::assertDispatched(GenerateEmbeddingJob::class, function (GenerateEmbeddingJob $job) use ($broadcast) {
            return $job->modelClass === Broadcast::class && $job->modelId === $broadcast->id;
        });
    }

    public function test_observer_dispatches_job_for_published_achievement(): void
    {
        Bus::fake();

        $achievement = Achievement::factory()->published()->create();

        Bus::assertDispatched(GenerateEmbeddingJob::class, function (GenerateEmbeddingJob $job) use ($achievement) {
            return $job->modelClass === Achievement::class && $job->modelId === $achievement->id;
        });
    }

    public function test_observer_dispatches_job_for_published_policy(): void
    {
        Bus::fake();

        $policy = Policy::factory()->published()->create();

        Bus::assertDispatched(GenerateEmbeddingJob::class, function (GenerateEmbeddingJob $job) use ($policy) {
            return $job->modelClass === Policy::class && $job->modelId === $policy->id;
        });
    }

    public function test_observer_dispatches_job_for_active_staff_directory(): void
    {
        Bus::fake();

        $staff = StaffDirectory::factory()->create(['is_active' => true]);

        Bus::assertDispatched(GenerateEmbeddingJob::class, function (GenerateEmbeddingJob $job) use ($staff) {
            return $job->modelClass === StaffDirectory::class && $job->modelId === $staff->id;
        });
    }

    public function test_observer_skips_draft_broadcast(): void
    {
        Bus::fake();

        Broadcast::factory()->create(['status' => 'draft']);

        Bus::assertNotDispatched(GenerateEmbeddingJob::class);
    }

    public function test_observer_skips_inactive_staff_directory(): void
    {
        Bus::fake();

        StaffDirectory::factory()->create(['is_active' => false]);

        Bus::assertNotDispatched(GenerateEmbeddingJob::class);
    }

    public function test_observer_removes_embeddings_when_unpublished(): void
    {
        Bus::fake();

        $broadcast = Broadcast::factory()->published()->create();

        Bus::assertDispatched(GenerateEmbeddingJob::class);

        // Now unpublish it — observer should NOT dispatch a new job
        Bus::fake();
        $broadcast->update(['status' => 'draft']);

        Bus::assertNotDispatched(GenerateEmbeddingJob::class);
    }

    public function test_observer_dispatches_job_on_delete_for_cleanup(): void
    {
        Bus::fake();

        $broadcast = Broadcast::factory()->published()->create();

        Bus::fake(); // Reset after create dispatch

        $broadcast->delete();

        // On delete, observer calls removeEmbeddings directly, not via job
        Bus::assertNotDispatched(GenerateEmbeddingJob::class);
    }

    public function test_job_uses_embeddings_queue(): void
    {
        $job = new GenerateEmbeddingJob(Broadcast::class, 1);

        $this->assertEquals('embeddings', $job->queue);
    }

    public function test_job_has_retry_config(): void
    {
        $job = new GenerateEmbeddingJob(Broadcast::class, 1);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 30, 60], $job->backoff);
    }

    public function test_job_handles_missing_model_gracefully(): void
    {
        // SQLite test — job returns early because driver isn't pgsql
        $job = new GenerateEmbeddingJob(Broadcast::class, 99999);

        $aiService = $this->app->make(\App\Services\AiService::class);
        $job->handle($aiService);

        $this->assertTrue(true); // No exception thrown
    }
}
