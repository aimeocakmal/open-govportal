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
| Chat Retention Days | `ai_chat_retention_days` | integer (min 7) | `90` |

**Chatbot Settings (identity, behavior, display):**

| Field | Settings key | Type | Default |
|-------|-------------|------|---------|
| Bot Name (BM) | `ai_chatbot_name_ms` | text | `Pembantu Digital` |
| Bot Name (EN) | `ai_chatbot_name_en` | text | `Digital Assistant` |
| Bot Avatar | `ai_chatbot_avatar` | file upload (image) | — (falls back to site logo) |
| Bot Persona (BM) | `ai_chatbot_persona_ms` | textarea | `Anda adalah pembantu AI rasmi Kementerian Digital Malaysia. Jawab dengan sopan dan formal.` |
| Bot Persona (EN) | `ai_chatbot_persona_en` | textarea | `You are the official AI assistant for the Ministry of Digital, Malaysia. Respond politely and formally.` |
| Language Preference | `ai_chatbot_language_preference` | select | `same_as_page` |
| Bot Restrictions (BM) | `ai_chatbot_restrictions_ms` | textarea | — |
| Bot Restrictions (EN) | `ai_chatbot_restrictions_en` | textarea | — |
| Display Location | `ai_chatbot_display_location` | select | `all_pages` |
| Display Pages | `ai_chatbot_display_pages` | JSON (route names) | `[]` (used when `specific_pages`) |
| Welcome Message (BM) | `ai_chatbot_welcome_ms` | textarea | `Selamat datang! Saya boleh membantu anda dengan maklumat mengenai Kementerian Digital.` |
| Welcome Message (EN) | `ai_chatbot_welcome_en` | textarea | `Welcome! I can help you with information about the Ministry of Digital.` |
| Input Placeholder (BM) | `ai_chatbot_placeholder_ms` | text | `Taip soalan anda...` |
| Input Placeholder (EN) | `ai_chatbot_placeholder_en` | text | `Type your question...` |
| Disclaimer Text (BM) | `ai_chatbot_disclaimer_ms` | textarea | — (falls back to `lang/ms/ai.php` default) |
| Disclaimer Text (EN) | `ai_chatbot_disclaimer_en` | textarea | — (falls back to `lang/en/ai.php` default) |

> **Language preference options:**
> - `same_as_page` — bot replies in the current page locale (default)
> - `always_ms` — bot always replies in Bahasa Malaysia
> - `always_en` — bot always replies in English
> - `user_choice` — shows a language toggle inside the chat widget; user picks their preferred response language
>
> **Display location options:**
> - `all_pages` — chat widget appears on every public page (default)
> - `homepage_only` — only on `/{locale}` homepage
> - `specific_pages` — only on routes listed in `ai_chatbot_display_pages` (matched via `Route::currentRouteName()`)
>
> **Bot persona + restrictions** are appended to the LLM system prompt. Persona sets the tone and identity; restrictions define guardrails (e.g. "Do not discuss political opinions", "Only answer questions related to the Ministry").

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
    public function chat(string $prompt, array $history = [], string $systemPrompt = '', string $locale = 'ms'): string {}

    public function grammarCheck(string $text, string $locale): string {}

    public function translate(string $text, string $from, string $to): string {}

    public function expand(string $text, string $locale): string {}

    public function summarise(string $text, string $locale): string {}

    public function tldr(string $text, string $locale): string {}

    public function generateFromPrompt(string $prompt, string $locale): string {}

    // Deferred — Prism PHP multimodal support varies by provider
    // public function generateFromImage(string $imageUrl, string $prompt, string $locale): string {}

    /** @return float[] — dimension depends on configured embedding model */
    public function embed(string $text): array {}

    public function isAvailable(): bool {}  // returns false if API key not configured

    /**
     * Returns usage data from the last chat() call.
     * @return object{promptTokens: ?int, completionTokens: ?int, durationMs: int}|null
     */
    public function getLastUsage(): ?object {}
}
```

**Provider resolution (inside `AiService`):**

All content operations (`grammarCheck`, `translate`, `expand`, `summarise`, `tldr`, `generateFromPrompt`) share a private `generate()` helper:

```php
private function generate(string $systemPrompt, string $userPrompt, string $operation, string $locale): string
{
    $provider = $this->resolveLlmProvider();   // → Provider enum
    $model    = $this->resolveLlmModel();       // → model string
    $apiKey   = $this->decryptSetting('ai_llm_api_key', config('ai.llm_api_key', ''));

    $response = $this->prism->text()
        ->using($provider, $model, $this->buildProviderConfig($apiKey))
        ->withSystemPrompt($systemPrompt)
        ->withPrompt($userPrompt)
        ->asText();

    // source: 'admin_editor' — all admin content operations route through generate()
    $this->logUsage($operation, $locale, $startTime, $provider, $model, $response, source: 'admin_editor');
    return $response->text;
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
| `$messages` | `array` | Conversation history (persisted to `ai_chat_messages` table) |
| `$input` | `string` | Current user input |
| `$isThinking` | `bool` | True while awaiting LLM response |

**Rate limiting:** Configurable via `ai_chatbot_rate_limit` setting (default 10 messages/hour/IP).

**Graceful degradation:** If `ai_chatbot_enabled = false` or the API key is not configured, the chat widget is hidden entirely — no error shown to public users.

**Bot identity:** Name, avatar, and persona are read from chatbot settings (see `ManageAiSettings → Chatbot Settings`). The persona text is appended to the LLM system prompt to shape the bot's tone and personality. The bot name and avatar are displayed in the chat header and next to bot messages.

**Language preference:** Controlled by `ai_chatbot_language_preference`:
- `same_as_page` (default) — response locale matches `app()->getLocale()`
- `always_ms` / `always_en` — forces a specific response language regardless of page locale
- `user_choice` — renders a language toggle inside the chat widget; user selects preferred language

**Restrictions/guardrails:** Admin-defined restriction text (`ai_chatbot_restrictions_{locale}`) is appended to the system prompt. The LLM is instructed to refuse queries outside these boundaries.

**Display location:** Controlled by `ai_chatbot_display_location`:
- `all_pages` (default) — widget renders on every public page
- `homepage_only` — only on `/{locale}` homepage route
- `specific_pages` — only on routes listed in `ai_chatbot_display_pages` (matched via `Route::currentRouteName()`)

**Welcome message:** The first bot message displayed when the chat window opens (before the user types). Read from `ai_chatbot_welcome_{locale}`.

**Privacy disclaimer:** Session modal on first open; content from `ai_chatbot_disclaimer_{locale}` setting (falls back to `lang/{locale}/ai.php` default). Acceptance stored in session, never persisted.

---

## Conversation Persistence

Public chatbot conversations are persisted to the database for admin review, analytics, and auto-purge.

### Database Table: `ai_chat_conversations`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `session_id` | varchar(100) | Laravel session ID (indexed) |
| `ip_address` | varchar(45) | Client IP (nullable) |
| `title` | varchar(255) | Auto-generated title (nullable until 3rd exchange) |
| `summary` | text | Conversation summary (nullable) |
| `tags` | json | Category tags — auto-generated, admin-editable (nullable) |
| `locale` | varchar(5) | Conversation locale (nullable) |
| `message_count` | unsigned int | Total messages in conversation |
| `total_prompt_tokens` | unsigned int | Aggregate prompt tokens |
| `total_completion_tokens` | unsigned int | Aggregate completion tokens |
| `started_at` | timestamp | Conversation start time |
| `last_message_at` | timestamp | Most recent message (nullable) |
| `ended_at` | timestamp | Conversation end time (nullable) |

### Database Table: `ai_chat_messages`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `conversation_id` | bigint (FK) | References `ai_chat_conversations.id` (cascade delete, indexed) |
| `role` | varchar(20) | `user` or `assistant` |
| `content` | text | Message body |
| `prompt_tokens` | unsigned int | Tokens used for this message (nullable) |
| `completion_tokens` | unsigned int | Tokens generated for this message (nullable) |
| `duration_ms` | unsigned int | LLM response time (nullable) |
| `created_at` | timestamp | Message timestamp |

### Auto-Generated Titles and Tags

After the **3rd exchange (6 messages)**, `GenerateConversationTitleJob` is dispatched (queued). The job:

1. Reads the first 6 messages (truncated to 300 chars each)
2. Sends a classification prompt to the configured LLM requesting JSON: `{"title": "...", "tags": [...]}`
3. Updates `title` and `tags` on the conversation record
4. Falls back to the first user message as title if JSON parsing fails

**Tag vocabulary:** `soalan-umum`, `dasar`, `perkhidmatan`, `teknikal`, `aduan`, `maklumat`, `cadangan`. Tags are admin-editable via `AiChatConversationResource`.

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
| ~~`AiGenerateFromImageAction`~~ | — | **Deferred** — Prism PHP multimodal support varies by provider |

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
| ~~Jana daripada Imej~~ | ~~Generate from image~~ | — | **Deferred** |

**Graceful degradation:** If `ai_admin_editor_enabled = false` or no API key is configured, all AI action buttons are hidden from the Filament form. No errors, no broken UI.

### Trait: `HasAiEditorActions` (`app/Filament/Concerns/`)

Provides reusable helper methods that return pre-configured action arrays per field type:

| Method | Actions included | Used on |
|--------|-----------------|---------|
| `richEditorAiActions()` | Grammar, Expand, Summarise, Translate, TLDR, Generate | RichEditor `content_{locale}` fields |
| `textareaAiActions()` | Grammar, Expand, Summarise, Translate | Textarea `description_{locale}` fields |
| `excerptAiActions()` | Grammar, Translate | Textarea `excerpt_{locale}` fields |

### Form Schemas with AI Actions

| Form Schema | Fields with AI actions |
|-------------|----------------------|
| `BroadcastForm` | `content_ms`, `content_en` (rich editor), `excerpt_ms`, `excerpt_en` (excerpt) |
| `AchievementForm` | `description_ms`, `description_en` (textarea) |
| `PolicyForm` | `description_ms`, `description_en` (textarea) |
| `StaticPageForm` | `content_ms`, `content_en` (rich editor), `excerpt_ms`, `excerpt_en` (excerpt) |

---

## Usage Logging

### Database Table: `ai_usage_logs`

Tracks all AI operations for cost monitoring. **Fully anonymised** — no user PII.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `operation` | varchar(50) | `grammar_check`, `translate`, `expand`, `summarise`, `tldr`, `generate`, `chat`, `embed` |
| `source` | varchar(30) | Origin of the call: `admin_editor`, `public_chat`, `admin_embedding` (indexed) |
| `locale` | varchar(5) | `ms` or `en` |
| `duration_ms` | unsigned int | Request duration in milliseconds |
| `prompt_tokens` | unsigned int | Input tokens (from Prism response) |
| `completion_tokens` | unsigned int | Output tokens (from Prism response) |
| `provider` | varchar(50) | Provider key (e.g. `anthropic`, `openai`) |
| `model` | varchar(100) | Model ID (e.g. `claude-sonnet-4-6`) |
| `created_at` | timestamp | Auto-set via `useCurrent()` (indexed) |

> No `user_id`, no `ip_address`, no `content` columns — PDPA-compliant by design.

### Source Tracking

Every `AiService` method logs its call with an explicit `source` value to distinguish where the request originated:

| `AiService` method | `source` value | Context |
|---------------------|---------------|---------|
| `chat()` | `public_chat` | Public chatbot conversations |
| `embed()` | `admin_embedding` | Embedding generation during RAG indexing |
| `generate()` (private) | `admin_editor` | All admin editor operations: grammar check, translate, expand, summarise, TLDR, generate from prompt |

The `source` column enables filtering in the `AiUsageDashboard` and in the auto-purge command, so admins can distinguish public chatbot cost from admin content editing cost.

### Retrieving Usage Data

`AiService::getLastUsage()` returns an object with `promptTokens`, `completionTokens`, and `durationMs` from the most recent `chat()` call. This is used by the `AiChat` Livewire component to update per-message and per-conversation token counters.

---

## Admin Monitoring Pages

### `AiUsageDashboard` (Custom Filament Page)

Located at `/admin/ai-usage-dashboard`. Provides a visual overview of all AI operations across the platform. Requires `manage_ai_settings` permission.

**Filters (top bar, 3-column layout):**

| Filter | Type | Notes |
|--------|------|-------|
| Date from | DatePicker | Start of date range |
| Date until | DatePicker | End of date range |
| Source | Select | `All sources`, `admin_editor`, `public_chat`, `admin_embedding` |

**Widgets:**

| Widget | Class | Description |
|--------|-------|-------------|
| Stats overview | `AiUsageStatsWidget` | 4 stat cards with sparklines: total requests, total prompt tokens, total completion tokens, average duration |
| Token chart | `AiTokenUsageChartWidget` | Line chart showing prompt vs completion tokens over time (grouped by day) |

Both widgets read the dashboard's filter state and update dynamically when filters change.

### `AiChatConversationResource` (Filament Resource)

Manages the `AiChatConversation` model. Navigation icon: `ChatBubbleLeftRight`. Requires `manage_ai_settings` permission.

**Pages:**

| Page | Route | Purpose |
|------|-------|---------|
| List | `/admin/ai-chat-conversations` | Searchable table of all conversations (title, locale, message count, tokens, dates, tags) |
| View | `/admin/ai-chat-conversations/{id}` | Read-only infolist with conversation metadata + full message thread rendered via custom Blade view |
| Edit | `/admin/ai-chat-conversations/{id}/edit` | Edit `title` and `tags` only (admin curation) |

> Creation is disabled (`canCreate() = false`) — conversations are created automatically by the public chatbot.

---

## Auto-Purge

### Artisan Command: `ai:purge-conversations`

Deletes old chat conversations and usage logs based on a configurable retention period.

```bash
# Run purge
php artisan ai:purge-conversations

# Preview what would be deleted (no changes)
php artisan ai:purge-conversations --dry-run
```

**Behaviour:**

1. Reads `ai_chat_retention_days` from the `settings` table (default: **90 days**, minimum: **7 days**)
2. Deletes `ai_chat_conversations` where `last_message_at < cutoff` (or `started_at < cutoff` if `last_message_at` is null). Cascade deletes associated `ai_chat_messages`.
3. Deletes `ai_usage_logs` where `created_at < cutoff`
4. `--dry-run` flag outputs counts without performing any deletions

**Scheduled execution:** Registered in `routes/console.php`, runs daily at **02:00**:

```php
Schedule::command('ai:purge-conversations')->dailyAt('02:00');
```

**Retention setting:** Configurable via the admin panel (`ManageAiSettings` or directly in the `settings` table):

| Settings key | Type | Default | Minimum |
|-------------|------|---------|---------|
| `ai_chat_retention_days` | integer | `90` | `7` |

---

## Rate Limiting & Cost Control

| Scope | Default limit | Configurable? |
|-------|--------------|--------------|
| Public chatbot | 10 messages/hour/IP | Yes — `ai_chatbot_rate_limit` setting |
| Admin AI editor | No hard limit | No — authenticated users only |
| Embedding job queue | 5 concurrent workers | Via `--max-jobs` on `queue:work` |

---

## PDPA Compliance Checklist

- [x] `content_embeddings` rows contain only public content — no names, ICs, emails
- [x] Chatbot conversations persisted to `ai_chat_conversations` / `ai_chat_messages` for admin review — auto-purged after configurable retention period (default 90 days) via `ai:purge-conversations`
- [x] AI usage logs (`ai_usage_logs`) contain no user PII — only operation type, source, duration, token count, provider used; auto-purged with conversations
- [x] Privacy disclaimer shown before first chat interaction
- [x] API keys stored encrypted in `settings` table; never logged
- [x] Admin can disable AI features completely via `ManageAiSettings`

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
