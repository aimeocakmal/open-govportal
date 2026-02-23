<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Widgets\AiTokenUsageChartWidget;
use App\Filament\Widgets\AiUsageStatsWidget;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class AiUsageDashboard extends Page
{
    use HasConfigurableNavigation;
    use HasFiltersForm;

    protected static string $sidebarKey = 'ai-usage-dashboard';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?int $navigationSort = 20;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    protected string $view = 'filament.pages.ai-usage-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.logs');
    }

    public static function getNavigationLabel(): string
    {
        return __('ai.usage_dashboard_title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_ai_settings') ?? false;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'sm' => 2,
                'lg' => 4,
                'xl' => 4,
            ])
            ->components([
                Select::make('source')
                    ->label(__('ai.source'))
                    ->options([
                        '' => __('ai.all_sources'),
                        'admin_editor' => __('ai.source_admin_editor'),
                        'public_chat' => __('ai.source_public_chat'),
                        'admin_embedding' => __('ai.source_admin_embedding'),
                    ])
                    ->default(''),
                DateRangePicker::make('dateRange')
                    ->label(__('ai.date_range'))
                    ->displayFormat('DD/MM/YYYY')
                    ->format('Y-m-d')
                    ->defaultLast30Days(),
            ]);
    }

    /**
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            AiUsageStatsWidget::class,
            AiTokenUsageChartWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 2;
    }
}
