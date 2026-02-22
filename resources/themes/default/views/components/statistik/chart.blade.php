@props(['chart', 'index' => 0])

@php
    $locale = app()->getLocale();
    $title = $chart['title_' . $locale] ?? $chart['title_ms'] ?? '';
    $description = $chart['description_' . $locale] ?? $chart['description_ms'] ?? '';
    $type = $chart['type'] ?? 'bar';
    $chartId = 'chart-' . $index;
@endphp

<div class="bg-white rounded-lg border border-border-light p-6">
    @if($title)
        <h3 class="font-heading font-semibold text-text text-body-lg mb-1">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="text-body-sm text-muted mb-4">{{ $description }}</p>
    @endif

    <div
        x-data="statistikChart(@js($chartId), @js($type), @js($chart['data'] ?? []))"
        x-init="init()"
        class="relative"
    >
        <canvas id="{{ $chartId }}" class="w-full" style="max-height: 400px;"></canvas>
    </div>
</div>
