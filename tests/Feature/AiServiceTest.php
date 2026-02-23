<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_available_returns_false_without_api_key(): void
    {
        $aiService = $this->app->make(AiService::class);

        $this->assertFalse($aiService->isAvailable());
    }

    public function test_is_available_returns_true_with_encrypted_api_key(): void
    {
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test-key'), 'encrypted');

        $aiService = $this->app->make(AiService::class);

        $this->assertTrue($aiService->isAvailable());
    }

    public function test_is_embedding_available_returns_false_without_any_key(): void
    {
        $aiService = $this->app->make(AiService::class);

        $this->assertFalse($aiService->isEmbeddingAvailable());
    }

    public function test_is_embedding_available_falls_back_to_llm_key(): void
    {
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-llm-key'), 'encrypted');

        $aiService = $this->app->make(AiService::class);

        $this->assertTrue($aiService->isEmbeddingAvailable());
    }

    public function test_is_embedding_available_uses_dedicated_embedding_key(): void
    {
        Setting::set('ai_embedding_api_key', Crypt::encrypt('sk-embed-key'), 'encrypted');

        $aiService = $this->app->make(AiService::class);

        $this->assertTrue($aiService->isEmbeddingAvailable());
    }

    public function test_config_fallback_used_when_no_settings(): void
    {
        config(['ai.llm_api_key' => '']);

        $aiService = $this->app->make(AiService::class);

        $this->assertFalse($aiService->isAvailable());
    }

    public function test_chat_returns_empty_string_without_api_key(): void
    {
        $aiService = $this->app->make(AiService::class);

        $result = $aiService->chat('Hello', [], 'You are helpful', 'ms');

        $this->assertEquals('', $result);
    }

    public function test_embed_returns_empty_array_without_api_key(): void
    {
        $aiService = $this->app->make(AiService::class);

        $result = $aiService->embed('some text');

        $this->assertEquals([], $result);
    }

    public function test_grammar_check_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->grammarCheck('text', 'ms');
    }

    public function test_translate_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->translate('text', 'ms', 'en');
    }

    public function test_expand_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->expand('text', 'ms');
    }

    public function test_summarise_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->summarise('text', 'ms');
    }

    public function test_tldr_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->tldr('text', 'ms');
    }

    public function test_generate_from_prompt_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->generateFromPrompt('prompt', 'ms');
    }

    public function test_generate_from_image_stub_throws_bad_method_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(AiService::class)->generateFromImage('url', 'prompt', 'ms');
    }

    public function test_is_available_handles_corrupted_encrypted_value(): void
    {
        Setting::set('ai_llm_api_key', 'not-encrypted-value', 'encrypted');

        $aiService = $this->app->make(AiService::class);

        // Should fall back to config default (empty) rather than throw
        $this->assertFalse($aiService->isAvailable());
    }
}
