<?php

return [
    /*
     * Themes available on the public portal.
     * Add a key here when a new resources/css/themes/{key}.css file is created.
     * The value is the human-readable display name.
     *
     * The active theme is stored in the `govportal_theme` cookie and falls back
     * to the `site_default_theme` key in the settings table.
     */
    'valid_themes' => [
        'default' => 'Default',
        // 'dark' => 'Dark',  // Phase 4
    ],

    /*
     * Fallback theme used when the cookie is absent and the settings table
     * cannot be queried (e.g. during installation before migrations run).
     */
    'fallback' => 'default',
];
