<?php

namespace App\Filament\Widgets;

use App\Models\AiUsageLog;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AiUsageStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $query = $this->buildFilteredQuery();

        $totalRequests = (clone $query)->count();
        $totalTokens = (clone $query)->selectRaw('COALESCE(SUM(prompt_tokens), 0) + COALESCE(SUM(completion_tokens), 0) as total')
            ->value('total') ?? 0;
        $avgDuration = (int) round((clone $query)->avg('duration_ms') ?? 0);
        $requestsToday = AiUsageLog::query()
            ->whereDate('created_at', today())
            ->when($this->getSourceFilter(), fn (Builder $q, string $source) => $q->where('source', $source))
            ->count();

        return [
            Stat::make(__('ai.total_requests'), number_format($totalRequests))
                ->description(__('ai.source').': '.($this->getSourceFilter() ? __('ai.source_'.$this->getSourceFilter()) : __('ai.all_sources')))
                ->chart($this->getSparklineData('count'))
                ->color('primary'),

            Stat::make(__('ai.total_tokens'), number_format($totalTokens))
                ->description(__('ai.prompt_tokens').' + '.__('ai.completion_tokens'))
                ->chart($this->getSparklineData('tokens'))
                ->color('success'),

            Stat::make(__('ai.avg_duration'), $avgDuration.'ms')
                ->chart($this->getSparklineData('duration'))
                ->color('warning'),

            Stat::make(__('ai.requests_today'), number_format($requestsToday))
                ->color('info'),
        ];
    }

    private function buildFilteredQuery(): Builder
    {
        [$startDate, $endDate] = $this->parseDateRange();
        $source = $this->getSourceFilter();

        return AiUsageLog::query()
            ->when($startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($source, fn (Builder $q, string $s) => $q->where('source', $s));
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

    /**
     * @return array<int, int|float>
     */
    private function getSparklineData(string $type): array
    {
        $days = collect(range(6, 0))->map(fn (int $i) => Carbon::today()->subDays($i)->toDateString());

        $query = AiUsageLog::query()
            ->whereDate('created_at', '>=', Carbon::today()->subDays(6))
            ->when($this->getSourceFilter(), fn (Builder $q, string $s) => $q->where('source', $s));

        $expression = match ($type) {
            'count' => 'COUNT(*)',
            'tokens' => 'COALESCE(SUM(prompt_tokens), 0) + COALESCE(SUM(completion_tokens), 0)',
            'duration' => 'COALESCE(AVG(duration_ms), 0)',
            default => 'COUNT(*)',
        };

        $results = (clone $query)
            ->selectRaw("DATE(created_at) as date, {$expression} as value")
            ->groupByRaw('DATE(created_at)')
            ->pluck('value', 'date');

        return $days->map(fn (string $date) => (float) ($results[$date] ?? 0))->values()->all();
    }

    private function getSourceFilter(): ?string
    {
        $source = $this->pageFilters['source'] ?? null;

        return $source !== '' && $source !== null ? $source : null;
    }
}
