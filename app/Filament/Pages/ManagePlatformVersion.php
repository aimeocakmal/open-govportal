<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManagePlatformVersion extends Page
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'manage-platform-version';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 8;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.settings.platform_version.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    protected string $view = 'filament.pages.manage-platform-version';

    public string $version = '';

    public string $releasedAt = '';

    /** @var array<int, array{type: string, items: list<string>}> */
    public array $changelog = [];

    /** @var list<array{version: string, released_at: string, changelog: list<array{type: string, items: list<string>}>}> */
    public array $history = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $path = base_path('version.json');

        if (! file_exists($path)) {
            $this->version = '0.0.0';
            $this->releasedAt = '';
            $this->changelog = [];
            $this->history = [];

            return;
        }

        $data = json_decode(file_get_contents($path), true);

        $this->version = $data['version'] ?? '0.0.0';
        $this->releasedAt = $data['released_at'] ?? '';
        $this->changelog = $data['changelog'] ?? [];
        $this->history = $data['history'] ?? [];
    }

    /**
     * Read version data from the flat file.
     *
     * @return array{version: string, released_at: string, changelog: list<array{type: string, items: list<string>}>, history: list<array{version: string, released_at: string, changelog: list<array{type: string, items: list<string>}>}>}
     */
    public static function readVersionFile(): array
    {
        $path = base_path('version.json');

        if (! file_exists($path)) {
            return ['version' => '0.0.0', 'released_at' => '', 'changelog' => [], 'history' => []];
        }

        $data = json_decode(file_get_contents($path), true);

        return [
            'version' => $data['version'] ?? '0.0.0',
            'released_at' => $data['released_at'] ?? '',
            'changelog' => $data['changelog'] ?? [],
            'history' => $data['history'] ?? [],
        ];
    }
}
