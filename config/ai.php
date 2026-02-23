<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LLM Provider Configuration
    |--------------------------------------------------------------------------
    |
    | These values serve as fallback defaults when the admin has not yet
    | configured AI settings via the ManageAiSettings Filament page.
    | The settings table always takes precedence over these values.
    |
    */

    'llm_provider' => env('AI_LLM_PROVIDER', 'anthropic'),
    'llm_model' => env('AI_LLM_MODEL', 'claude-sonnet-4-6'),
    'llm_api_key' => env('AI_LLM_API_KEY', ''),
    'llm_base_url' => env('AI_LLM_BASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Embedding Provider Configuration
    |--------------------------------------------------------------------------
    */

    'embedding_provider' => env('AI_EMBEDDING_PROVIDER', 'openai'),
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    'embedding_api_key' => env('AI_EMBEDDING_API_KEY', ''),
    'embedding_dimension' => (int) env('AI_EMBEDDING_DIMENSION', 1536),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'chatbot_enabled' => (bool) env('AI_CHATBOT_ENABLED', false),
    'admin_editor_enabled' => (bool) env('AI_ADMIN_EDITOR_ENABLED', false),
    'chatbot_rate_limit' => (int) env('AI_CHATBOT_RATE_LIMIT', 10),
];
