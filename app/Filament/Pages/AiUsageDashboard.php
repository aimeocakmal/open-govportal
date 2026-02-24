<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Widgets\AiTokenUsageChartWidget;
use App\Filament\Widgets\AiUsageStatsWidget;
use App\Services\AiPurgeService;
use App\Services\MediaDiskService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
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

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getArchiveAction(),
        ];
    }

    protected function getArchiveAction(): Action
    {
        $purgeService = app(AiPurgeService::class);
        $counts = $purgeService->countEligible();
        $retentionDays = $purgeService->getRetentionDays();

        return Action::make('archive')
            ->label(__('ai.archive_action'))
            ->icon(Heroicon::OutlinedArchiveBox)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(__('ai.archive_confirm_heading'))
            ->modalDescription(__('ai.archive_confirm_description', [
                'conversations' => $counts['conversations'],
                'logs' => $counts['logs'],
                'days' => $retentionDays,
            ]))
            ->modalSubmitActionLabel(__('ai.archive_confirm_button'))
            ->action(function (): void {
                $result = app(AiPurgeService::class)->archiveAndPurge(app(MediaDiskService::class));

                if ($result['archive'] === null) {
                    Notification::make()
                        ->info()
                        ->title(__('ai.purge_nothing'))
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title(__('ai.archive_success'))
                    ->body(__('ai.purge_completed', [
                        'conversations' => $result['conversations'],
                        'logs' => $result['logs'],
                        'archive' => $result['archive'],
                    ]))
                    ->persistent()
                    ->send();
            });
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
