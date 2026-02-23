<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;
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

    public function test_grammar_check_returns_empty_without_api_key(): void
    {
        $result = $this->app->make(AiService::class)->grammarCheck('text with errors', 'ms');

        $this->assertEquals('', $result);
    }

    public function test_grammar_check_returns_corrected_text(): void
    {
        $this->setUpApiKey();
        $fake = Prism::fake([
            TextResponseFake::make()->withText('Teks yang betul.')->withUsage(new Usage(10, 5)),
        ]);

        $result = $this->app->make(AiService::class)->grammarCheck('teks yang salah', 'ms');

        $this->assertEquals('Teks yang betul.', $result);
        $fake->assertCallCount(1);
    }

    public function test_grammar_check_logs_usage(): void
    {
        $this->setUpApiKey();
        Prism::fake([
            TextResponseFake::make()->withText('Fixed.')->withUsage(new Usage(15, 8)),
        ]);

        $this->app->make(AiService::class)->grammarCheck('broken text', 'en');

        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'grammar_check',
            'locale' => 'en',
            'provider' => 'anthropic',
            'prompt_tokens' => 15,
            'completion_tokens' => 8,
        ]);
    }

    public function test_translate_returns_empty_without_api_key(): void
    {
        $result = $this->app->make(AiService::class)->translate('text', 'ms', 'en');

        $this->assertEquals('', $result);
    }

    public function test_translate_returns_translated_text(): void
    {
        $this->setUpApiKey();
        $fake = Prism::fake([
            TextResponseFake::make()->withText('Hello world.')->withUsage(new Usage(20, 10)),
        ]);

        $result = $this->app->make(AiService::class)->translate('Halo dunia.', 'ms', 'en');

        $this->assertEquals('Hello world.', $result);
        $fake->assertCallCount(1);
    }

    public function test_translate_logs_usage_with_target_locale(): void
    {
        $this->setUpApiKey();
        Prism::fake([
            TextResponseFake::make()->withText('Translated.')->withUsage(new Usage(12, 6)),
        ]);

        $this->app->make(AiService::class)->translate('Original.', 'en', 'ms');

        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'translate',
            'locale' => 'ms',
        ]);
    }

    public function test_expand_returns_empty_without_api_key(): void
    {
        $result = $this->app->make(AiService::class)->expand('short text', 'ms');

        $this->assertEquals('', $result);
    }

    public function test_expand_returns_expanded_text(): void
    {
        $this->setUpApiKey();
        $fake = Prism::fake([
            TextResponseFake::make()->withText('This is a much longer expanded version of the text.')->withUsage(new Usage(10, 30)),
        ]);

        $result = $this->app->make(AiService::class)->expand('short text', 'en');

        $this->assertEquals('This is a much longer expanded version of the text.', $result);
        $fake->assertCallCount(1);
    }

    public function test_summarise_returns_empty_without_api_key(): void
    {
        $result = $this->app->make(AiService::class)->summarise('long text here', 'ms');

        $this->assertEquals('', $result);
    }

    public function test_summarise_returns_summarised_text(): void
    {
        $this->setUpApiKey();
        $fake = Prism::fake([
            TextResponseFake::make()->withText('Summary.')->withUsage(new Usage(50, 10)),
        ]);

        $result = $this->app->make(AiService::class)->summarise('very long text with many details', 'ms');

        $this->assertEquals('Summary.', $result);
        $fake->assertCallCount(1);
    }

    public function test_tldr_returns_empty_without_api_key(): void
    {
        $result = $this->app->make(AiService::class)->tldr('text', 'ms');

        $this->assertEquals('', $result);
    }

    public function test_tldr_returns_short_summary(): void
    {
        $this->setUpApiKey();
        $fake = Prism::fake([
            TextResponseFake::make()->withText('TLDR: This is a summary.')->withUsage(new Usage(40, 8)),
        ]);

        $result = $this->app->make(AiService::class)->tldr('long article content', 'en');

        $this->assertEquals('TLDR: This is a summary.', $result);
        $fake->assertCallCount(1);
    }

    public function test_generate_from_prompt_returns_empty_without_api_key(): void
    {
        $result = $this->app->make(AiService::class)->generateFromPrompt('write about cats', 'ms');

        $this->assertEquals('', $result);
    }

    public function test_generate_from_prompt_returns_generated_content(): void
    {
        $this->setUpApiKey();
        $fake = Prism::fake([
            TextResponseFake::make()->withText('<p>Generated content about cats.</p>')->withUsage(new Usage(15, 25)),
        ]);

        $result = $this->app->make(AiService::class)->generateFromPrompt('write about cats', 'ms');

        $this->assertEquals('<p>Generated content about cats.</p>', $result);
        $fake->assertCallCount(1);
    }

    public function test_generate_from_prompt_logs_usage(): void
    {
        $this->setUpApiKey();
        Prism::fake([
            TextResponseFake::make()->withText('Content.')->withUsage(new Usage(10, 20)),
        ]);

        $this->app->make(AiService::class)->generateFromPrompt('write something', 'ms');

        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'generate',
            'locale' => 'ms',
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
        ]);
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

    public function test_usage_log_records_duration(): void
    {
        $this->setUpApiKey();
        Prism::fake([
            TextResponseFake::make()->withText('result')->withUsage(new Usage(5, 5)),
        ]);

        $this->app->make(AiService::class)->grammarCheck('test', 'ms');

        $log = AiUsageLog::first();
        $this->assertNotNull($log);
        $this->assertGreaterThanOrEqual(0, $log->duration_ms);
    }

    public function test_multiple_operations_create_separate_logs(): void
    {
        $this->setUpApiKey();
        Prism::fake([
            TextResponseFake::make()->withText('fixed')->withUsage(new Usage(5, 5)),
            TextResponseFake::make()->withText('expanded')->withUsage(new Usage(10, 15)),
        ]);

        $service = $this->app->make(AiService::class);
        $service->grammarCheck('text', 'ms');
        $service->expand('text', 'en');

        $this->assertDatabaseCount('ai_usage_logs', 2);
        $this->assertDatabaseHas('ai_usage_logs', ['operation' => 'grammar_check']);
        $this->assertDatabaseHas('ai_usage_logs', ['operation' => 'expand']);
    }

    private function setUpApiKey(): void
    {
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test-key'), 'encrypted');
    }
}
