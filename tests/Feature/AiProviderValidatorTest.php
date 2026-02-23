<?php

namespace Tests\Feature;

use App\Services\AiProviderValidator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiProviderValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_llm_models_returns_known_models_for_anthropic(): void
    {
        $models = AiProviderValidator::llmModels('anthropic');

        $this->assertArrayHasKey('claude-sonnet-4-6', $models);
        $this->assertArrayHasKey('claude-opus-4-6', $models);
        $this->assertArrayHasKey('claude-haiku-4-5', $models);
    }

    public function test_llm_models_returns_known_models_for_openai(): void
    {
        $models = AiProviderValidator::llmModels('openai');

        $this->assertArrayHasKey('gpt-4o', $models);
        $this->assertArrayHasKey('gpt-4o-mini', $models);
    }

    public function test_llm_models_returns_empty_for_openai_compatible(): void
    {
        $this->assertEmpty(AiProviderValidator::llmModels('openai-compatible'));
    }

    public function test_llm_models_returns_empty_for_unknown_provider(): void
    {
        $this->assertEmpty(AiProviderValidator::llmModels('nonexistent'));
    }

    public function test_embedding_models_returns_known_models_for_openai(): void
    {
        $models = AiProviderValidator::embeddingModels('openai');

        $this->assertArrayHasKey('text-embedding-3-small', $models);
        $this->assertArrayHasKey('text-embedding-3-large', $models);
    }

    public function test_embedding_models_returns_known_models_for_cohere(): void
    {
        $models = AiProviderValidator::embeddingModels('cohere');

        $this->assertArrayHasKey('embed-multilingual-v3.0', $models);
    }

    public function test_empty_api_key_passes_validation(): void
    {
        $this->assertTrue(AiProviderValidator::validateApiKey('openai', ''));
    }

    public function test_openai_valid_key_returns_true(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => []], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('openai', 'sk-valid'));
    }

    public function test_openai_invalid_key_returns_false(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response([], 401)]);

        $this->assertFalse(AiProviderValidator::validateApiKey('openai', 'sk-bad'));
    }

    public function test_anthropic_valid_key_returns_true(): void
    {
        Http::fake(['api.anthropic.com/v1/messages' => Http::response(['id' => 'msg_1'], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('anthropic', 'sk-ant-valid'));
    }

    public function test_anthropic_unauthorized_returns_false(): void
    {
        Http::fake(['api.anthropic.com/v1/messages' => Http::response([], 401)]);

        $this->assertFalse(AiProviderValidator::validateApiKey('anthropic', 'sk-ant-bad'));
    }

    public function test_anthropic_rate_limited_treated_as_valid(): void
    {
        Http::fake(['api.anthropic.com/v1/messages' => Http::response([], 429)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('anthropic', 'sk-ant-ok'));
    }

    public function test_google_valid_key_returns_true(): void
    {
        Http::fake(['generativelanguage.googleapis.com/v1/models*' => Http::response(['models' => []], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('google', 'AIza-valid'));
    }

    public function test_groq_uses_openai_pattern(): void
    {
        Http::fake(['api.groq.com/openai/v1/models' => Http::response(['data' => []], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('groq', 'gsk-valid'));
    }

    public function test_deepseek_uses_openai_pattern(): void
    {
        Http::fake(['api.deepseek.com/v1/models' => Http::response(['data' => []], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('deepseek', 'sk-valid'));
    }

    public function test_ollama_validates_reachability(): void
    {
        Http::fake(['localhost:11434/api/tags' => Http::response(['models' => []], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('ollama', '', ''));
    }

    public function test_unknown_provider_returns_true(): void
    {
        $this->assertTrue(AiProviderValidator::validateApiKey('unknown', 'key'));
    }

    public function test_network_exception_returns_false(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::failedConnection()]);

        $this->assertFalse(AiProviderValidator::validateApiKey('openai', 'sk-key'));
    }

    public function test_validation_result_is_cached(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => []], 200)]);

        AiProviderValidator::validateApiKey('openai', 'sk-test');
        AiProviderValidator::validateApiKey('openai', 'sk-test');

        Http::assertSentCount(1);
    }

    public function test_different_keys_get_separate_cache_entries(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => []], 200)]);

        AiProviderValidator::validateApiKey('openai', 'sk-key-1');
        AiProviderValidator::validateApiKey('openai', 'sk-key-2');

        Http::assertSentCount(2);
    }

    public function test_openai_compatible_without_base_url_returns_false(): void
    {
        $this->assertFalse(AiProviderValidator::validateApiKey('openai-compatible', 'sk-key', ''));
    }

    public function test_openai_compatible_with_base_url_validates(): void
    {
        Http::fake(['custom-api.example.com/models' => Http::response(['data' => []], 200)]);

        $this->assertTrue(AiProviderValidator::validateApiKey('openai-compatible', 'sk-key', 'https://custom-api.example.com'));
    }
}
