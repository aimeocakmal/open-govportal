<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiProviderValidator
{
    /**
     * Known LLM models per provider key.
     *
     * @return array<string, string>
     */
    public static function llmModels(string $provider): array
    {
        return match ($provider) {
            'anthropic' => [
                'claude-sonnet-4-6' => 'Claude Sonnet 4.6',
                'claude-opus-4-6' => 'Claude Opus 4.6',
                'claude-haiku-4-5' => 'Claude Haiku 4.5',
            ],
            'openai' => [
                'gpt-4o' => 'GPT-4o',
                'gpt-4o-mini' => 'GPT-4o Mini',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'o1' => 'o1',
                'o1-mini' => 'o1-mini',
                'o3-mini' => 'o3-mini',
            ],
            'google' => [
                'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            ],
            'groq' => [
                'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile',
                'mixtral-8x7b-32768' => 'Mixtral 8x7B 32768',
            ],
            'mistral' => [
                'mistral-large-latest' => 'Mistral Large',
                'mistral-small-latest' => 'Mistral Small',
            ],
            'xai' => [
                'grok-2' => 'Grok 2',
                'grok-beta' => 'Grok Beta',
            ],
            'deepseek' => [
                'deepseek-chat' => 'DeepSeek Chat',
                'deepseek-coder' => 'DeepSeek Coder',
                'deepseek-reasoner' => 'DeepSeek Reasoner',
            ],
            'ollama' => [
                'llama3.3' => 'Llama 3.3',
                'qwen2.5' => 'Qwen 2.5',
                'deepseek-r1' => 'DeepSeek R1',
                'mistral' => 'Mistral',
            ],
            'openai-compatible' => [],
            default => [],
        };
    }

    /**
     * Known embedding models per provider key.
     *
     * @return array<string, string>
     */
    public static function embeddingModels(string $provider): array
    {
        return match ($provider) {
            'openai' => [
                'text-embedding-3-small' => 'text-embedding-3-small (1536d)',
                'text-embedding-3-large' => 'text-embedding-3-large (3072d)',
                'text-embedding-ada-002' => 'text-embedding-ada-002 (1536d)',
            ],
            'google' => [
                'text-embedding-004' => 'text-embedding-004 (768d)',
            ],
            'cohere' => [
                'embed-multilingual-v3.0' => 'embed-multilingual-v3.0 (1024d)',
                'embed-english-v3.0' => 'embed-english-v3.0 (1024d)',
            ],
            'voyageai' => [
                'voyage-3' => 'voyage-3 (1024d)',
                'voyage-3-lite' => 'voyage-3-lite (512d)',
            ],
            'ollama' => [
                'nomic-embed-text' => 'nomic-embed-text (768d)',
                'mxbai-embed-large' => 'mxbai-embed-large (1024d)',
            ],
            default => [],
        };
    }

    /**
     * Validate an API key against the given provider.
     *
     * Returns true if the key is valid or empty (empty = feature disabled, not an error).
     * Uses a 60-second cache keyed on provider+key+baseUrl to avoid repeated calls.
     */
    public static function validateApiKey(string $provider, string $apiKey, string $baseUrl = ''): bool
    {
        if ($apiKey === '') {
            return true;
        }

        $cacheKey = 'ai_key_valid:'.md5($provider.$apiKey.$baseUrl);

        return Cache::remember($cacheKey, 60, function () use ($provider, $apiKey, $baseUrl): bool {
            try {
                return match ($provider) {
                    'openai' => self::validateOpenAiKey($apiKey, 'https://api.openai.com/v1'),
                    'anthropic' => self::validateAnthropicKey($apiKey),
                    'google' => self::validateGoogleKey($apiKey),
                    'groq' => self::validateOpenAiKey($apiKey, 'https://api.groq.com/openai/v1'),
                    'mistral' => self::validateOpenAiKey($apiKey, 'https://api.mistral.ai/v1'),
                    'xai' => self::validateOpenAiKey($apiKey, 'https://api.x.ai/v1'),
                    'deepseek' => self::validateOpenAiKey($apiKey, 'https://api.deepseek.com/v1'),
                    'ollama' => self::validateOllamaKey($baseUrl),
                    'openai-compatible' => $baseUrl !== ''
                        ? self::validateOpenAiKey($apiKey, rtrim($baseUrl, '/'))
                        : false,
                    default => true,
                };
            } catch (\Throwable $e) {
                Log::warning('AI API key validation failed', [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }

    /**
     * OpenAI-pattern: GET /v1/models with Bearer token.
     */
    private static function validateOpenAiKey(string $apiKey, string $baseUrl): bool
    {
        $response = Http::timeout(10)
            ->withToken($apiKey)
            ->get("{$baseUrl}/models");

        return $response->successful();
    }

    /**
     * Anthropic: POST /v1/messages with minimal payload.
     * 401/403 = invalid key. 200/429/500 = key is valid.
     */
    private static function validateAnthropicKey(string $apiKey): bool
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5',
                'max_tokens' => 1,
                'messages' => [['role' => 'user', 'content' => 'hi']],
            ]);

        return $response->status() !== 401 && $response->status() !== 403;
    }

    /**
     * Google Gemini: GET models list with API key query param.
     */
    private static function validateGoogleKey(string $apiKey): bool
    {
        $response = Http::timeout(10)
            ->get('https://generativelanguage.googleapis.com/v1/models', [
                'key' => $apiKey,
            ]);

        return $response->successful();
    }

    /**
     * Ollama: GET /api/tags — no auth needed, just checks reachability.
     */
    private static function validateOllamaKey(string $baseUrl): bool
    {
        $url = $baseUrl !== '' ? rtrim($baseUrl, '/') : 'http://localhost:11434';

        $response = Http::timeout(5)->get("{$url}/api/tags");

        return $response->successful();
    }
}
