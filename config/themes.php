<?php

return [
    /*
     * Base path where theme directories are stored.
     * Each theme is a subdirectory containing a theme.json manifest.
     */
    'path' => resource_path('themes'),

    /*
     * Fallback theme used when the cookie is absent and the settings table
     * cannot be queried (e.g. during installation before migrations run).
     */
    'fallback' => 'default',

    /*
     * How long (in seconds) to cache the discovered themes list.
     * Set to 0 to disable caching (useful during development).
     */
    'cache_ttl' => env('THEME_CACHE_TTL', 86400),
];
