<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ThemeService
{
    private string $active = 'default';

    /** @var array<string, array<string, mixed>>|null */
    private ?array $discovered = null;

    public function getActive(): string
    {
        return $this->active;
    }

    public function setActive(string $name): void
    {
        $themes = $this->discover();

        $this->active = isset($themes[$name]) ? $name : config('themes.fallback', 'default');
    }

    /**
     * Scan the themes directory for valid theme.json manifests.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        if ($this->discovered !== null) {
            return $this->discovered;
        }

        $ttl = (int) config('themes.cache_ttl', 86400);

        $this->discovered = Cache::remember('themes:discovered', $ttl, function (): array {
            $themes = [];
            $themesPath = config('themes.path', resource_path('themes'));

            if (! File::isDirectory($themesPath)) {
                return ['default' => $this->defaultManifest()];
            }

            foreach (File::directories($themesPath) as $dir) {
                $manifestPath = $dir.'/theme.json';

                if (! File::exists($manifestPath)) {
                    continue;
                }

                $manifest = json_decode(File::get($manifestPath), true);

                if (! is_array($manifest) || empty($manifest['name'])) {
                    continue;
                }

                $themes[$manifest['name']] = $manifest;
            }

            if (empty($themes)) {
                $themes['default'] = $this->defaultManifest();
            }

            return $themes;
        });

        return $this->discovered;
    }

    public function getViewsPath(string $theme): string
    {
        return config('themes.path', resource_path('themes')).'/'.$theme.'/views';
    }

    /**
     * Return the Vite entry points for a given theme.
     *
     * @return array{css: string, js: string}
     */
    public function getViteEntries(string $theme): array
    {
        $themes = $this->discover();
        $manifest = $themes[$theme] ?? $themes['default'] ?? $this->defaultManifest();

        return [
            'css' => 'resources/themes/'.$theme.'/'.($manifest['css'] ?? 'css/app.css'),
            'js' => 'resources/themes/'.$theme.'/'.($manifest['js'] ?? 'js/app.js'),
        ];
    }

    /**
     * Return theme options for admin dropdowns and theme switcher.
     *
     * @return array<string, string>
     */
    public function getThemeOptions(string $locale): array
    {
        $themes = $this->discover();
        $options = [];

        foreach ($themes as $name => $manifest) {
            $label = $manifest['label'][$locale]
                ?? $manifest['label']['en']
                ?? ucfirst($name);
            $options[$name] = $label;
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultManifest(): array
    {
        return [
            'name' => 'default',
            'label' => ['ms' => 'Lalai', 'en' => 'Default'],
            'version' => '1.0.0',
            'author' => 'GovPortal',
            'css' => 'css/app.css',
            'js' => 'js/app.js',
        ];
    }
}
