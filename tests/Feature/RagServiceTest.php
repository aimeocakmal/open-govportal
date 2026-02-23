<?php

namespace Tests\Feature;

use App\Services\RagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RagServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_context_formats_chunks_as_numbered_sources(): void
    {
        $ragService = $this->app->make(RagService::class);

        $chunks = [
            ['content' => 'First chunk content', 'metadata' => ['title' => 'Title A']],
            ['content' => 'Second chunk content', 'metadata' => ['title' => 'Title B']],
        ];

        $context = $ragService->buildContext($chunks);

        $this->assertStringContainsString('1]', $context);
        $this->assertStringContainsString('2]', $context);
        $this->assertStringContainsString('First chunk content', $context);
        $this->assertStringContainsString('Second chunk content', $context);
    }

    public function test_build_context_returns_empty_string_for_empty_chunks(): void
    {
        $ragService = $this->app->make(RagService::class);

        $context = $ragService->buildContext([]);

        $this->assertEquals('', $context);
    }

    public function test_retrieve_chunks_returns_empty_on_sqlite(): void
    {
        // Tests run on SQLite, so this should gracefully return empty
        $ragService = $this->app->make(RagService::class);

        $result = $ragService->retrieveChunks('test query', 'ms');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_build_context_single_chunk(): void
    {
        $ragService = $this->app->make(RagService::class);

        $chunks = [
            ['content' => 'Only one chunk here', 'metadata' => []],
        ];

        $context = $ragService->buildContext($chunks);

        $this->assertStringContainsString('1]', $context);
        $this->assertStringContainsString('Only one chunk here', $context);
        $this->assertStringNotContainsString('2]', $context);
    }
}
