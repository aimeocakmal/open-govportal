# AI Features

OpenGovPortal includes two AI-powered features:

1. **Public AI Chatbot** — Floating chat widget on all public pages; answers questions using RAG retrieval from embedded site content.
2. **Admin AI Content Editor** — AI-assisted actions inside Filament's RichEditor for content creation and editing.

Both features are **provider-agnostic**: the LLM and embedding provider are configured by the admin through a Filament settings page. No code changes are needed to switch between Anthropic, OpenAI, Google Gemini, Qwen, Moonshot, or any other supported provider.

---

## Technology Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| AI framework | **Prism PHP** (`echolabsdev/prism`) | Unified interface for all AI provider calls in Laravel |
| LLM provider | **Admin-configurable** (see below) | Anthropic, OpenAI, Google, Groq, Mistral, Ollama, or any OpenAI-compatible endpoint |
| Embedding provider | **Admin-configurable** (see below) | OpenAI, Google, Cohere, VoyageAI, Ollama |
| Vector storage | **pgvector** (PostgreSQL extension) | `content_embeddings` table — no separate vector DB |
| Chatbot component | **Livewire 4 `AiChat`** | Server-side streaming; no client-side fetch |
| Admin actions | **Filament custom actions** (`app/Filament/Actions/Ai/`) | Injected into RichEditor fields |
| Provider config | **`ManageAiSettings`** Filament settings page | API keys, model selection, feature flags — stored encrypted in `settings` table |

---

## Supported Providers

### LLM Providers (for chatbot + admin content operations)

| Provider key | Service | Example models |
|-------------|---------|---------------|
| `anthropic` | Anthropic (Claude) | `claude-sonnet-4-6`, `claude-opus-4-6`, `claude-haiku-4-5` |
| `openai` | OpenAI | `gpt-4o`, `gpt-4o-mini`, `gpt-4-turbo`, `gpt-3.5-turbo` |
| `google` | Google Gemini | `gemini-2.0-flash`, `gemini-1.5-pro`, `gemini-1.5-flash` |
| `groq` | Groq (fast inference) | `llama-3.3-70b-versatile`, `mixtral-8x7b-32768`, `gemma-7b-it` |
| `mistral` | Mistral AI | `mistral-large-latest`, `mistral-small-latest` |
| `xai` | xAI | `grok-2`, `grok-beta` |
| `ollama` | Ollama (local/self-hosted) | `llama3.3`, `qwen2.5`, `deepseek-r1`, any Ollama model |
| `openai-compatible` | OpenAI-compatible endpoint | Qwen (DashScope), Moonshot, DeepSeek, Together AI, Fireworks, etc. |

> **Qwen, Moonshot, DeepSeek, and similar providers** use the `openai-compatible` key with a custom base URL configured in the settings page. They expose an OpenAI-compatible API that Prism PHP can call directly.

### Embedding Providers

| Provider key | Service | Available models | Dimensions |
|-------------|---------|-----------------|-----------|
| `openai` | OpenAI | `text-embedding-3-small`, `text-embedding-3-large`, `text-embedding-ada-002` | 1536 / 3072 / 1536 |
| `google` | Google | `text-embedding-004` | 768 |
| `cohere` | Cohere | `embed-multilingual-v3.0`, `embed-english-v3.0` | 1024 |
| `voyageai` | VoyageAI | `voyage-3`, `voyage-3-lite` | 1024 / 512 |
| `ollama` | Ollama (local) | `nomic-embed-text`, `mxbai-embed-large` | 768 / 1024 |

> **Important — Embedding dimension consistency:** The `content_embeddings.embedding` column is a fixed-dimension vector column in pgvector. Changing embedding providers that produce a different number of dimensions **requires full re-indexing** (`php artisan govportal:reindex-embeddings`). A warning is shown in the `ManageAiSettings` page if the configured dimension differs from the stored dimension.

---

## Admin Configuration: `ManageAiSettings`

A dedicated Filament settings page at `/admin/ai-settings` allows admins to configure all AI behaviour without touching code.

### Settings Fields

**LLM Configuration:**

| Field | Settings key | Type | Default |
|-------|-------------|------|---------|
| LLM Provider | `ai_llm_provider` | select | `anthropic` |
| LLM Model | `ai_llm_model` | text | `claude-sonnet-4-6` |
| LLM API Key | `ai_llm_api_key` | password (encrypted) | — |
| LLM Base URL | `ai_llm_base_url` | text (optional) | — (for OpenAI-compatible endpoints) |

**Embedding Configuration:**

| Field | Settings key | Type | Default |
|-------|-------------|------|---------|
| Embedding Provider | `ai_embedding_provider` | select | `openai` |
| Embedding Model | `ai_embedding_model` | text | `text-embedding-3-small` |
| Embedding API Key | `ai_embedding_api_key` | password (encrypted) | — (defaults to LLM key if same provider) |
| Embedding Dimension | `ai_embedding_dimension` | integer (read-only display) | 1536 |

**Feature Flags:**

| Field | Settings key | Type | Default |
|-------|-------------|------|---------|
| Chatbot Enabled | `ai_chatbot_enabled` | boolean | `false` |
| Admin Editor Enabled | `ai_admin_editor_enabled` | boolean | `false` |
| Chatbot Rate Limit | `ai_chatbot_rate_limit` | integer (msg/hour/IP) | `10` |

> API keys are stored **encrypted** using Laravel's `Crypt::encrypt()` / `Crypt::decrypt()`. The `type` column in the `settings` table is set to `'encrypted'` for these keys.

### OpenAI-Compatible Providers (Qwen, Moonshot, etc.)

Set `ai_llm_provider = openai-compatible` and configure:

| Provider | Base URL | Notes |
|----------|---------|-------|
| Qwen (Alibaba DashScope) | `https://dashscope.aliyuncs.com/compatible-mode/v1` | Use DashScope API key |
| Moonshot | `https://api.moonshot.cn/v1` | Supports `moonshot-v1-8k`, `moonshot-v1-32k` |
| DeepSeek | `https://api.deepseek.com/v1` | `deepseek-chat`, `deepseek-coder` |
| Together AI | `https://api.together.xyz/v1` | Access to many open models |
| Fireworks AI | `https://api.fireworks.ai/inference/v1` | Fast open model inference |

---

## Environment Variables

`.env` variables serve as **defaults / fallbacks** only. The admin panel overrides them via the `settings` table.

```env
# Fallback AI configuration (overridden by ManageAiSettings in admin)
AI_LLM_PROVIDER=anthropic
AI_LLM_MODEL=claude-sonnet-4-6
AI_LLM_API_KEY=sk-ant-...
AI_LLM_BASE_URL=          # leave empty unless using openai-compatible

AI_EMBEDDING_PROVIDER=openai
AI_EMBEDDING_MODEL=text-embedding-3-small
AI_EMBEDDING_API_KEY=sk-...
AI_EMBEDDING_DIMENSION=1536

# Feature flags (also overrideable from admin)
AI_CHATBOT_ENABLED=false
AI_ADMIN_EDITOR_ENABLED=false
AI_CHATBOT_RATE_LIMIT=10
```

> Use `.env` for local development. In production, configure via the admin panel — this avoids redeployment when switching providers.

---

## Service Classes

### `App\Services\AiService`

Single entry point for all LLM and embedding calls. Reads active provider configuration from `settings` table (cached 5 minutes). Never call Prism PHP directly from controllers or Livewire components.

```php
class AiService
{
    public function chat(string $prompt, array $history = [], string $locale = 'ms'): string {}

    public function grammarCheck(string $text, string $locale): string {}

    public function translate(string $text, string $from, string $to): string {}

    public function expand(string $text, string $locale): string {}

    public function summarise(string $text, string $locale): string {}

    public function tldr(string $text, string $locale): string {}

    public function generateFromPrompt(string $prompt, string $locale): string {}

    public function generateFromImage(string $imageUrl, string $prompt, string $locale): string {}

    /** @return float[] — dimension depends on configured embedding model */
    public function embed(string $text): array {}

    public function isAvailable(): bool {}  // returns false if API key not configured
}
```

**Provider resolution (inside `AiService`):**

```php
private function resolveLlm(): Text
{
    $provider = Setting::get('ai_llm_provider', config('ai.llm_provider', 'anthropic'));
    $model    = Setting::get('ai_llm_model',    config('ai.llm_model',    'claude-sonnet-4-6'));
    $apiKey   = Crypt::decrypt(Setting::get('ai_llm_api_key', ''));
    $baseUrl  = Setting::get('ai_llm_base_url', '');

    $prism = Prism::text()->using($provider, $model);

    if ($apiKey) {
        $prism = $prism->withClientOptions(['api_key' => $apiKey]);
    }
    if ($baseUrl && $provider === 'openai-compatible') {
        $prism = $prism->withClientOptions(['base_url' => $baseUrl]);
    }

    return $prism;
}
```

### `App\Services\RagService`

Handles the vector retrieval step of the RAG pipeline. Uses the configured embedding provider to embed queries.

```php
class RagService
{
    public function __construct(private AiService $ai) {}

    /** @return array{content: string, metadata: array}[] */
    public function retrieveChunks(string $query, string $locale, int $limit = 5): array {}

    public function buildContext(array $chunks): string {}
}
```

---

## RAG Pipeline

### Write Path (Content Save → Embedding)

```
Model saved (Broadcast, Achievement, Policy, StaffDirectory)
  → EmbeddingObserver::saved()
  → dispatch GenerateEmbeddingJob (queued, 'embeddings' queue)
      1. Chunk content into ~500-token segments (ms and en separately)
      2. Call AiService::embed() → resolves configured embedding provider via Prism PHP
      3. Upsert into content_embeddings:
         - embeddable_type / embeddable_id (morphic)
         - chunk_index (int, 0-based)
         - locale ('ms' or 'en')
         - content (TEXT — chunk text, for debugging)
         - embedding (vector(n) — dimension from ai_embedding_dimension setting)
         - metadata (JSON — model title, url slug, type)
```

### Read Path (User Query → Response)

```
User message submitted via AiChat Livewire component
  1. AiService::embed(userMessage) → embedding vector
  2. RagService::retrieveChunks() → pgvector cosine similarity, top 5, filtered by locale
  3. Build prompt: system prompt + context chunks + session history (last 10 turns)
  4. AiService::chat() → resolves configured LLM provider → streams response
  5. Append to PHP session conversation history
```

### Embedding Dimension Change Warning

When an admin changes the embedding provider/model in `ManageAiSettings`, and the new model produces a different vector dimension than `ai_embedding_dimension`, the settings page displays:

> ⚠ **Re-indexing required.** Changing the embedding model from `{old}` (1536 dims) to `{new}` (768 dims) requires re-generating all content embeddings. Run `php artisan govportal:reindex-embeddings` after saving. Existing chatbot responses may be inaccurate until re-indexing completes.

### Database Table: `content_embeddings`

```sql
-- The vector dimension is set at table creation based on the initial embedding model.
-- Changing providers that output different dimensions requires DROP + recreate this column.
CREATE TABLE content_embeddings (
    id              BIGSERIAL PRIMARY KEY,
    embeddable_type VARCHAR(255) NOT NULL,
    embeddable_id   BIGINT NOT NULL,
    chunk_index     SMALLINT NOT NULL DEFAULT 0,
    locale          VARCHAR(5)  NOT NULL,          -- 'ms' or 'en'
    content         TEXT        NOT NULL,           -- raw chunk text (for debugging)
    embedding       vector(1536) NOT NULL,          -- dimension set by PGVECTOR_DIMENSION env
    metadata        JSONB,                          -- {title, slug, url, type, provider, model}
    created_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Morphic index
CREATE INDEX idx_content_embeddings_morphic
    ON content_embeddings (embeddable_type, embeddable_id);

-- pgvector IVFFlat index (add when > 10,000 rows)
CREATE INDEX idx_content_embeddings_vector
    ON content_embeddings USING ivfflat (embedding vector_cosine_ops)
    WITH (lists = 100);
```

> The `PGVECTOR_DIMENSION` env variable (default `1536`) controls the column dimension at migration time. Change it before running migrations if using a provider with a different dimension.

### Models That Generate Embeddings

| Model | Fields chunked | Notes |
|-------|---------------|-------|
| `Broadcast` | `title_{locale}` + `content_{locale}` + `excerpt_{locale}` | Both ms and en |
| `Achievement` | `title_{locale}` + `description_{locale}` | Both ms and en |
| `Policy` | `title_{locale}` + `description_{locale}` | Both ms and en |
| `StaffDirectory` | `name` + `position_{locale}` + `department_{locale}` | Both ms and en |

---

## Public AI Chatbot (`AiChat`)

| Property | Type | Description |
|----------|------|-------------|
| `$messages` | `array` | Session conversation history (not persisted to DB) |
| `$input` | `string` | Current user input |
| `$isThinking` | `bool` | True while awaiting LLM response |

**Rate limiting:** 10 messages/hour/IP (configurable via `ai_chatbot_rate_limit` setting).

**Graceful degradation:** If `ai_chatbot_enabled = false` or the API key is not configured, the chat widget is hidden entirely — no error shown to public users.

**Bilingual:** System prompt adapts to `app()->getLocale()`. Response language matches the current page locale.

**Privacy disclaimer:** Session modal on first open; acceptance stored in session, never persisted.

---

## Admin AI Content Editor

### Filament Action Classes (`app/Filament/Actions/Ai/`)

| Class | File | Operation |
|-------|------|-----------|
| `AiGrammarAction` | `AiGrammarAction.php` | Grammar check; `$locale` constructor param |
| `AiTranslateAction` | `AiTranslateAction.php` | Translation; `$from`, `$to` locale params |
| `AiExpandAction` | `AiExpandAction.php` | Expand text |
| `AiSummariseAction` | `AiSummariseAction.php` | Summarise field content |
| `AiTldrAction` | `AiTldrAction.php` | TLDR → fills excerpt field |
| `AiGenerateAction` | `AiGenerateAction.php` | Generate from text prompt (modal) |
| `AiGenerateFromImageAction` | `AiGenerateFromImageAction.php` | Generate from image URL + prompt |

**Available operations in the admin editor:**

| Action label (BM) | Operation | Input | Output |
|-------------------|-----------|-------|--------|
| Semak Tatabahasa BM | Grammar check (ms) | Field content | Corrected text + summary |
| Grammar Check (EN) | Grammar check (en) | Field content | Corrected text + summary |
| Terjemah → EN | BM → EN translation | `content_ms` | Fills `content_en` |
| Terjemah → BM | EN → BM translation | `content_en` | Fills `content_ms` |
| Kembangkan | Expand / elaborate | Selected text | Extended version |
| Ringkaskan | Summarise | Field content | Condensed version |
| Jana TLDR | Auto TLDR | `content_{locale}` | 2-3 sentences → `excerpt_{locale}` |
| Jana daripada Prompt | Generate from prompt | Modal: text prompt + locale | Draft content |
| Jana daripada Imej | Generate from image | Modal: image URL/upload + prompt | Caption/content |

**Graceful degradation:** If `ai_admin_editor_enabled = false` or no API key is configured, all AI action buttons are hidden from the Filament form. No errors, no broken UI.

---

## Rate Limiting & Cost Control

| Scope | Default limit | Configurable? |
|-------|--------------|--------------|
| Public chatbot | 10 messages/hour/IP | Yes — `ai_chatbot_rate_limit` setting |
| Admin AI editor | No hard limit | No — authenticated users only |
| Embedding job queue | 5 concurrent workers | Via `--max-jobs` on `queue:work` |

---

## PDPA Compliance Checklist

- [ ] `content_embeddings` rows contain only public content — no names, ICs, emails
- [ ] Chatbot session history not persisted to database
- [ ] AI usage logs (`ai_usage_logs`) contain no user PII — only operation type, duration, token count, provider used
- [ ] Privacy disclaimer shown before first chat interaction
- [ ] API keys stored encrypted in `settings` table; never logged
- [ ] Admin can disable AI features completely via `ManageAiSettings`

---

## Adding a New Embeddable Model

1. Create the model per the standard agentic-coding.md workflow.
2. Register `EmbeddingObserver` in `AppServiceProvider::boot()`:
   ```php
   NewModel::observe(EmbeddingObserver::class);
   ```
3. Add the model to `GenerateEmbeddingJob::$fieldMap`:
   ```php
   NewModel::class => ['title_ms', 'title_en', 'body_ms', 'body_en'],
   ```
4. Run `php artisan queue:work --queue=embeddings` after seeding.
5. Update the Models table above.

---

## Switching Providers (Operational Guide)

### Switch LLM (e.g., Anthropic → OpenAI)

1. Log into `/admin` → AI Settings
2. Change "LLM Provider" to `openai`, "LLM Model" to `gpt-4o`
3. Enter OpenAI API key
4. Save — takes effect immediately (settings cached 5 minutes)
5. Test chatbot and admin editor in staging

### Switch to OpenAI-compatible endpoint (e.g., Qwen)

1. Log into `/admin` → AI Settings
2. Set "LLM Provider" to `openai-compatible`
3. Set "LLM Model" to `qwen-max` (or your chosen Qwen model)
4. Set "LLM API Key" to your DashScope API key
5. Set "LLM Base URL" to `https://dashscope.aliyuncs.com/compatible-mode/v1`
6. Save

### Switch Embedding Provider

1. Log into `/admin` → AI Settings → Embedding Configuration
2. Change provider and model
3. Note the warning if dimension changes
4. Save → run `php artisan govportal:reindex-embeddings` to rebuild vectors
5. Monitor queue for completion before testing chatbot

---

## Cost Estimation (Reference only — prices vary by provider)

| Component | Provider example | Approx monthly cost |
|-----------|----------------|-------------------|
| LLM calls (chatbot) | Anthropic claude-sonnet-4-6 | ~$30/10K queries |
| LLM calls (admin editor) | Any provider | ~$5–10/500 ops |
| Embeddings (query) | OpenAI text-embedding-3-small | ~$0.05/10K queries |
| Embeddings (index) | OpenAI text-embedding-3-small | ~$0.50 initial run |
| LLM calls (chatbot) | Google Gemini 1.5 Flash | ~$5/10K queries |
| LLM calls (chatbot) | Groq Llama 3.3 70B | ~$2/10K queries |

*Use the admin settings page to experiment with cheaper providers without redeploying.*

---

## Related Docs

- [Architecture → AI Services Layer](architecture.md#ai-services-layer)
- [Pages & Features → AI Chatbot](pages-features.md)
- [Pages & Features → Admin AI Editor](pages-features.md)
- [Agentic Coding → AI Naming Conventions](agentic-coding.md)
- [Agentic Coding → AI Validation Commands](agentic-coding.md)
- [Conversion Timeline → Phase 6](conversion-timeline.md)
