<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class AiService
{
    public function __construct(private Prism $prism) {}

    /**
     * Send a chat message with optional conversation history and RAG context.
     */
    public function chat(string $prompt, array $history = [], string $systemPrompt = '', string $locale = 'ms'): string
    {
        $provider = $this->resolveLlmProvider();
        $model = $this->resolveLlmModel();
        $apiKey = $this->decryptSetting('ai_llm_api_key', config('ai.llm_api_key', ''));

        if ($apiKey === '') {
            Log::warning('AiService::chat called but no LLM API key configured.');

            return '';
        }

        $pendingRequest = $this->prism->text()
            ->using($provider, $model, $this->buildProviderConfig($apiKey));

        if ($systemPrompt !== '') {
            $pendingRequest->withSystemPrompt($systemPrompt);
        }

        // Build messages from conversation history
        $messages = [];
        foreach ($history as $turn) {
            if (($turn['role'] ?? '') === 'user') {
                $messages[] = new UserMessage($turn['content'] ?? '');
            } elseif (($turn['role'] ?? '') === 'assistant') {
                $messages[] = new AssistantMessage($turn['content'] ?? '');
            }
        }

        if (! empty($messages)) {
            $pendingRequest->withMessages($messages);
        }

        $pendingRequest->withPrompt($prompt);

        try {
            $response = $pendingRequest->asText();

            return $response->text;
        } catch (\Throwable $e) {
            Log::error('AiService::chat failed', [
                'provider' => $provider->value,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Generate embeddings for a given text.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        $provider = $this->resolveEmbeddingProvider();
        $model = $this->resolveEmbeddingModel();
        $apiKey = $this->resolveEmbeddingApiKey();

        if ($apiKey === '') {
            Log::warning('AiService::embed called but no embedding API key configured.');

            return [];
        }

        try {
            $response = $this->prism->embeddings()
                ->using($provider, $model, $this->buildProviderConfig($apiKey, forEmbedding: true))
                ->fromInput($text)
                ->asEmbeddings();

            return $response->embeddings[0] ?? [];
        } catch (\Throwable $e) {
            Log::error('AiService::embed failed', [
                'provider' => $provider->value,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if the AI service is available (API key configured).
     */
    public function isAvailable(): bool
    {
        $llmKey = $this->decryptSetting('ai_llm_api_key', config('ai.llm_api_key', ''));

        return $llmKey !== '';
    }

    /**
     * Check if embeddings are available (embedding API key configured).
     */
    public function isEmbeddingAvailable(): bool
    {
        return $this->resolveEmbeddingApiKey() !== '';
    }

    /**
     * Grammar check — stub for Week 12.
     */
    public function grammarCheck(string $text, string $locale): string
    {
        throw new \BadMethodCallException('AiService::grammarCheck is not yet implemented (Week 12).');
    }

    /**
     * Translate — stub for Week 12.
     */
    public function translate(string $text, string $from, string $to): string
    {
        throw new \BadMethodCallException('AiService::translate is not yet implemented (Week 12).');
    }

    /**
     * Expand — stub for Week 12.
     */
    public function expand(string $text, string $locale): string
    {
        throw new \BadMethodCallException('AiService::expand is not yet implemented (Week 12).');
    }

    /**
     * Summarise — stub for Week 12.
     */
    public function summarise(string $text, string $locale): string
    {
        throw new \BadMethodCallException('AiService::summarise is not yet implemented (Week 12).');
    }

    /**
     * TLDR — stub for Week 12.
     */
    public function tldr(string $text, string $locale): string
    {
        throw new \BadMethodCallException('AiService::tldr is not yet implemented (Week 12).');
    }

    /**
     * Generate from prompt — stub for Week 12.
     */
    public function generateFromPrompt(string $prompt, string $locale): string
    {
        throw new \BadMethodCallException('AiService::generateFromPrompt is not yet implemented (Week 12).');
    }

    /**
     * Generate from image — stub for Week 12.
     */
    public function generateFromImage(string $imageUrl, string $prompt, string $locale): string
    {
        throw new \BadMethodCallException('AiService::generateFromImage is not yet implemented (Week 12).');
    }

    private function resolveLlmProvider(): Provider
    {
        $key = Setting::get('ai_llm_provider', config('ai.llm_provider', 'anthropic'));

        return $this->mapProvider($key);
    }

    private function resolveLlmModel(): string
    {
        return Setting::get('ai_llm_model', config('ai.llm_model', 'claude-sonnet-4-6'));
    }

    private function resolveEmbeddingProvider(): Provider
    {
        $key = Setting::get('ai_embedding_provider', config('ai.embedding_provider', 'openai'));

        return $this->mapProvider($key);
    }

    private function resolveEmbeddingModel(): string
    {
        return Setting::get('ai_embedding_model', config('ai.embedding_model', 'text-embedding-3-small'));
    }

    private function resolveEmbeddingApiKey(): string
    {
        $embeddingKey = $this->decryptSetting('ai_embedding_api_key', config('ai.embedding_api_key', ''));

        // Fall back to LLM key if embedding key is empty
        if ($embeddingKey === '') {
            return $this->decryptSetting('ai_llm_api_key', config('ai.llm_api_key', ''));
        }

        return $embeddingKey;
    }

    private function mapProvider(string $key): Provider
    {
        return match ($key) {
            'anthropic' => Provider::Anthropic,
            'openai' => Provider::OpenAI,
            'google', 'gemini' => Provider::Gemini,
            'groq' => Provider::Groq,
            'mistral' => Provider::Mistral,
            'xai' => Provider::XAI,
            'ollama' => Provider::Ollama,
            'deepseek' => Provider::DeepSeek,
            'voyageai' => Provider::VoyageAI,
            'openai-compatible', 'openrouter' => Provider::OpenAI,
            default => Provider::Anthropic,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProviderConfig(string $apiKey, bool $forEmbedding = false): array
    {
        $config = ['api_key' => $apiKey];

        if (! $forEmbedding) {
            $baseUrl = Setting::get('ai_llm_base_url', config('ai.llm_base_url', ''));
            $provider = Setting::get('ai_llm_provider', config('ai.llm_provider', 'anthropic'));

            if ($baseUrl !== '' && $provider === 'openai-compatible') {
                $config['url'] = $baseUrl;
            }
        }

        return $config;
    }

    private function decryptSetting(string $key, string $fallback = ''): string
    {
        $raw = Setting::get($key, '');

        if ($raw === '' || $raw === null) {
            return $fallback;
        }

        try {
            return Crypt::decrypt($raw);
        } catch (DecryptException) {
            return $fallback;
        }
    }
}
