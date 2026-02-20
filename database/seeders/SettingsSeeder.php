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
