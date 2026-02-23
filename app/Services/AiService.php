<?php

namespace App\Services;

use App\Models\AiUsageLog;
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
    private ?object $lastUsage = null;

    public function __construct(private Prism $prism) {}

    /**
     * Get usage data from the last chat() or generate() call.
     *
     * @return object{promptTokens: ?int, completionTokens: ?int, durationMs: int}|null
     */
    public function getLastUsage(): ?object
    {
        return $this->lastUsage;
    }

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

        // Build messages from conversation history.
        // withPrompt() and withMessages() are mutually exclusive in Prism PHP,
        // so when history exists, append the current prompt as the last UserMessage.
        $messages = [];
        foreach ($history as $turn) {
            if (($turn['role'] ?? '') === 'user') {
                $messages[] = new UserMessage($turn['content'] ?? '');
            } elseif (($turn['role'] ?? '') === 'assistant') {
                $messages[] = new AssistantMessage($turn['content'] ?? '');
            }
        }

        if (! empty($messages)) {
            $messages[] = new UserMessage($prompt);
            $pendingRequest->withMessages($messages);
        } else {
            $pendingRequest->withPrompt($prompt);
        }

        $startTime = microtime(true);

        try {
            $response = $pendingRequest->asText();
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            $this->lastUsage = (object) [
                'promptTokens' => $response->usage->promptTokens ?? null,
                'completionTokens' => $response->usage->completionTokens ?? null,
                'durationMs' => $durationMs,
            ];

            $this->logUsage(
                operation: 'chat',
                locale: $locale,
                startTime: $startTime,
                provider: $provider,
                model: $model,
                response: $response,
                source: 'public_chat',
            );

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

        $startTime = microtime(true);

        try {
            $response = $this->prism->embeddings()
                ->using($provider, $model, $this->buildProviderConfig($apiKey, forEmbedding: true))
                ->fromInput($text)
                ->asEmbeddings();

            $this->logUsage(
                operation: 'embed',
                locale: '',
                startTime: $startTime,
                provider: $provider,
                model: $model,
                response: $response,
                source: 'admin_embedding',
            );

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
     * Check and fix grammar/spelling errors.
     */
    public function grammarCheck(string $text, string $locale): string
    {
        $langName = $locale === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = "You are a professional grammar and spelling checker for {$langName}. "
            .'Fix all grammar, spelling, and punctuation errors in the provided text. '
            .'Return ONLY the corrected text without any explanations, comments, or markup. '
            .'Preserve the original HTML tags if present. Do not change the meaning or tone.';

        return $this->generate($systemPrompt, $text, 'grammar_check', $locale);
    }

    /**
     * Translate text between locales.
     */
    public function translate(string $text, string $from, string $to): string
    {
        $fromName = $from === 'ms' ? 'Bahasa Malaysia' : 'English';
        $toName = $to === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = "You are a professional translator. Translate the following text from {$fromName} to {$toName}. "
            .'Return ONLY the translated text without any explanations or comments. '
            .'Preserve the original HTML tags and formatting if present. '
            .'Use natural, fluent language appropriate for a government website.';

        return $this->generate($systemPrompt, $text, 'translate', $to);
    }

    /**
     * Expand/elaborate text with more detail.
     */
    public function expand(string $text, string $locale): string
    {
        $langName = $locale === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = "You are a professional content writer for {$langName}. "
            .'Expand and elaborate on the provided text with more detail, examples, and context. '
            .'Maintain the original tone, style, and meaning. '
            .'Return ONLY the expanded text without any explanations. '
            .'Preserve HTML tags if present.';

        return $this->generate($systemPrompt, $text, 'expand', $locale);
    }

    /**
     * Summarise field content.
     */
    public function summarise(string $text, string $locale): string
    {
        $langName = $locale === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = "You are a professional summariser for {$langName}. "
            .'Condense the provided text while keeping all key points and important information. '
            .'Return ONLY the summarised text without any explanations. '
            .'Preserve HTML tags if present.';

        return $this->generate($systemPrompt, $text, 'summarise', $locale);
    }

    /**
     * Generate a TLDR summary as a bulleted list with a hook.
     */
    public function tldr(string $text, string $locale): string
    {
        $langName = $locale === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = "You are a TLDR generator for {$langName}. "
            .'Create a TL;DR using 2 to 4 bullet points. '
            .'Do NOT just list the topics — summarise the conclusions and key takeaways. '
            .'End with a final bullet that gives the reader a reason to keep reading the full article. '
            .'Return ONLY an HTML unordered list (<ul><li>…</li></ul>) with no other markup or text.';

        return $this->generate($systemPrompt, $text, 'tldr', $locale);
    }

    /**
     * Write a 30-50 word excerpt based on article content.
     */
    public function writeExcerpt(string $text, string $locale): string
    {
        $langName = $locale === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = 'You are a professional editor for a government website. '
            ."Write a concise excerpt of 30 to 50 words in {$langName} that summarises the provided article. "
            .'The excerpt must be written in the SAME language as the article. '
            .'Return ONLY plain text (no HTML, no quotes). '
            .'Do not start with phrases like "This article" or "In this article".';

        return $this->generate($systemPrompt, $text, 'write_excerpt', $locale);
    }

    /**
     * Generate content from a text prompt.
     */
    public function generateFromPrompt(string $prompt, string $locale): string
    {
        $langName = $locale === 'ms' ? 'Bahasa Malaysia' : 'English';
        $systemPrompt = "You are a professional content writer for a government website in {$langName}. "
            .'Generate well-structured content based on the user\'s prompt. '
            .'Use a formal but accessible tone appropriate for a government website. '
            .'Return ONLY the generated content. You may use HTML formatting (paragraphs, lists, headings).';

        return $this->generate($systemPrompt, $prompt, 'generate', $locale);
    }

    /**
     * Generate from image — deferred (Prism PHP multimodal support varies by provider).
     */
    public function generateFromImage(string $imageUrl, string $prompt, string $locale): string
    {
        throw new \BadMethodCallException('AiService::generateFromImage is deferred — Prism PHP multimodal support varies by provider.');
    }

    /**
     * Generic text generation helper used by all content operations.
     */
    private function generate(string $systemPrompt, string $userPrompt, string $operation = 'generate', string $locale = 'ms'): string
    {
        $provider = $this->resolveLlmProvider();
        $model = $this->resolveLlmModel();
        $apiKey = $this->decryptSetting('ai_llm_api_key', config('ai.llm_api_key', ''));

        if ($apiKey === '') {
            Log::warning("AiService::{$operation} called but no LLM API key configured.");

            return '';
        }

        $startTime = microtime(true);

        try {
            $response = $this->prism->text()
                ->using($provider, $model, $this->buildProviderConfig($apiKey))
                ->withSystemPrompt($systemPrompt)
                ->withPrompt($userPrompt)
                ->asText();

            $this->logUsage(
                operation: $operation,
                locale: $locale,
                startTime: $startTime,
                provider: $provider,
                model: $model,
                response: $response,
                source: 'admin_editor',
            );

            return $response->text;
        } catch (\Throwable $e) {
            Log::error("AiService::{$operation} failed", [
                'provider' => $provider->value,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    private function logUsage(string $operation, string $locale, float $startTime, Provider $provider, string $model, mixed $response, string $source = 'admin_editor'): void
    {
        try {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            AiUsageLog::create([
                'operation' => $operation,
                'source' => $source,
                'locale' => $locale,
                'duration_ms' => $durationMs,
                'prompt_tokens' => $response->usage->promptTokens ?? null,
                'completion_tokens' => $response->usage->completionTokens ?? null,
                'provider' => $provider->value,
                'model' => $model,
            ]);
        } catch (\Throwable $e) {
            Log::warning('AiService::logUsage failed', ['error' => $e->getMessage()]);
        }
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
