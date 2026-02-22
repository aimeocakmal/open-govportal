<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * @property-read Schema $form
 */
class ManageStatistik extends Page
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'manage-statistik';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 3;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.settings.statistik.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    protected string $view = 'filament.pages.manage-statistik';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'statistik_charts' => Setting::get('statistik_charts', $this->getDefaultCharts()),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make(__('filament.settings.statistik.charts_section'))
                        ->description(__('filament.settings.statistik.charts_section_desc'))
                        ->schema([
                            Textarea::make('statistik_charts')
                                ->label(__('filament.settings.statistik.charts_json'))
                                ->helperText(__('filament.settings.statistik.charts_json_help'))
                                ->rows(20)
                                ->required(),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament.actions.save'))
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                            Action::make('reset')
                                ->label(__('filament.actions.reset'))
                                ->color('gray')
                                ->action(fn () => $this->mount()),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $json = $data['statistik_charts'] ?? '[]';

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Notification::make()
                ->danger()
                ->title(__('filament.settings.statistik.invalid_json'))
                ->send();

            return;
        }

        Setting::set('statistik_charts', json_encode($decoded), 'json');
        Cache::tags(['statistik'])->flush();

        Notification::make()
            ->success()
            ->title(__('filament.settings.statistik.saved'))
            ->send();
    }

    private function getDefaultCharts(): string
    {
        return json_encode([
            [
                'title_ms' => 'Pelawat Laman Web',
                'title_en' => 'Website Visitors',
                'description_ms' => 'Bilangan pelawat bulanan',
                'description_en' => 'Monthly visitor count',
                'type' => 'line',
                'data' => [
                    'labels' => ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun'],
                    'datasets' => [
                        [
                            'label' => 'Pelawat / Visitors',
                            'data' => [12000, 15000, 13500, 18000, 22000, 25000],
                            'color' => '#2563EB',
                        ],
                    ],
                ],
            ],
            [
                'title_ms' => 'Perkhidmatan Digital',
                'title_en' => 'Digital Services',
                'description_ms' => 'Penggunaan perkhidmatan digital mengikut kategori',
                'description_en' => 'Digital service usage by category',
                'type' => 'bar',
                'data' => [
                    'labels' => ['MyGov', 'e-Khidmat', 'Data Terbuka', 'Portal Rasmi'],
                    'datasets' => [
                        [
                            'label' => 'Pengguna / Users',
                            'data' => [45000, 32000, 18000, 67000],
                            'color' => '#2563EB',
                        ],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
