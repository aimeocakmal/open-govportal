<?php

namespace App\Filament\Widgets;

use App\Models\AiUsageLog;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AiTokenUsageChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('ai.token_usage_over_time');
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->parseDateRange();
        $source = $this->pageFilters['source'] ?? null;

        if (! $startDate) {
            $startDate = Carbon::today()->subDays(29)->toDateString();
        }
        if (! $endDate) {
            $endDate = Carbon::today()->toDateString();
        }

        $effectiveSource = ($source !== '' && $source !== null) ? $source : null;

        $results = AiUsageLog::query()
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(prompt_tokens), 0) as prompt, COALESCE(SUM(completion_tokens), 0) as completion')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when($effectiveSource, fn (Builder $q, string $s) => $q->where('source', $s))
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy('date');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $labels = [];
        $promptData = [];
        $completionData = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('M d');
            $promptData[] = (int) ($results[$key]->prompt ?? 0);
            $completionData[] = (int) ($results[$key]->completion ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('ai.prompt_tokens'),
                    'data' => $promptData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => __('ai.completion_tokens'),
                    'data' => $completionData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function parseDateRange(): array
    {
        $range = $this->pageFilters['dateRange'] ?? null;

        if (! $range || ! str_contains($range, ' - ')) {
            return [null, null];
        }

        $parts = explode(' - ', $range, 2);

        try {
            $start = Carbon::createFromFormat('d/m/Y', trim($parts[0]))->toDateString();
            $end = Carbon::createFromFormat('d/m/Y', trim($parts[1]))->toDateString();

            return [$start, $end];
        } catch (\Throwable) {
            return [null, null];
        }
    }
}
