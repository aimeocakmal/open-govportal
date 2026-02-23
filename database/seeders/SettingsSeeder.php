<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'site_name_ms',          'value' => 'Kementerian Digital Malaysia', 'type' => 'string'],
            ['key' => 'site_name_en',           'value' => 'Ministry of Digital Malaysia', 'type' => 'string'],
            ['key' => 'site_description_ms',    'value' => 'Portal rasmi Kementerian Digital Malaysia.', 'type' => 'string'],
            ['key' => 'site_description_en',    'value' => 'Official portal of the Ministry of Digital Malaysia.', 'type' => 'string'],
            ['key' => 'site_default_theme',     'value' => 'default', 'type' => 'string'],
            ['key' => 'google_analytics_id',    'value' => '', 'type' => 'string'],
            ['key' => 'facebook_url',           'value' => '', 'type' => 'string'],
            ['key' => 'twitter_url',            'value' => '', 'type' => 'string'],
            ['key' => 'instagram_url',          'value' => '', 'type' => 'string'],
            ['key' => 'youtube_url',            'value' => '', 'type' => 'string'],

            // AI settings
            ['key' => 'ai_llm_provider',              'value' => 'anthropic', 'type' => 'string'],
            ['key' => 'ai_llm_model',                  'value' => 'claude-sonnet-4-6', 'type' => 'string'],
            ['key' => 'ai_llm_api_key',                'value' => '', 'type' => 'encrypted'],
            ['key' => 'ai_llm_base_url',               'value' => '', 'type' => 'string'],
            ['key' => 'ai_embedding_provider',         'value' => 'openai', 'type' => 'string'],
            ['key' => 'ai_embedding_model',            'value' => 'text-embedding-3-small', 'type' => 'string'],
            ['key' => 'ai_embedding_api_key',          'value' => '', 'type' => 'encrypted'],
            ['key' => 'ai_embedding_dimension',        'value' => '1536', 'type' => 'string'],
            ['key' => 'ai_chatbot_enabled',            'value' => '0', 'type' => 'string'],
            ['key' => 'ai_admin_editor_enabled',       'value' => '0', 'type' => 'string'],
            ['key' => 'ai_chatbot_rate_limit',         'value' => '10', 'type' => 'string'],
            ['key' => 'ai_chatbot_name_ms',            'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_name_en',            'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_avatar',             'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_persona_ms',         'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_persona_en',         'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_language_preference', 'value' => 'same_as_page', 'type' => 'string'],
            ['key' => 'ai_chatbot_restrictions_ms',    'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_restrictions_en',    'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_display_location',   'value' => 'all_pages', 'type' => 'string'],
            ['key' => 'ai_chatbot_display_pages',      'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_welcome_ms',         'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_welcome_en',         'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_placeholder_ms',     'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_placeholder_en',     'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_disclaimer_ms',      'value' => '', 'type' => 'string'],
            ['key' => 'ai_chatbot_disclaimer_en',      'value' => '', 'type' => 'string'],
        ];

        foreach ($defaults as $setting) {
            DB::table('settings')->upsert(
                $setting,
                ['key'],
                ['value', 'type']
            );
        }
    }
}
