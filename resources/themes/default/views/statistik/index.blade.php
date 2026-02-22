<x-layouts.app :title="__('statistik.title')">

    @php
        $locale = app()->getLocale();
    @endphp

    @push('seo')
        <meta name="description" content="{{ __('statistik.description') }}">
        <meta property="og:title" content="{{ __('statistik.title') }}">
        <meta property="og:description" content="{{ __('statistik.description') }}">
    @endpush

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('statistik.title'), 'url' => ''],
    ]" />

    <section class="py-10 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page header --}}
            <div class="mb-8">
                <h1 class="font-heading text-heading-md font-semibold text-text">
                    {{ __('statistik.title') }}
                </h1>
                <p class="mt-2 text-body-md text-muted max-w-2xl">
                    {{ __('statistik.description') }}
                </p>
            </div>

            @if(empty($charts))
                <div class="rounded-lg border border-border-light bg-bg-washed px-6 py-12 text-center">
                    <p class="text-body-md text-muted">{{ __('statistik.no_data') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($charts as $index => $chart)
                        <x-statistik.chart :chart="$chart" :index="$index" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if(!empty($charts))
        @push('seo')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        @endpush

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('statistikChart', (canvasId, chartType, chartData) => ({
                    chart: null,
                    init() {
                        const ctx = document.getElementById(canvasId);
                        if (!ctx || !chartData) return;

                        const config = {
                            type: chartType,
                            data: {
                                labels: chartData.labels || [],
                                datasets: (chartData.datasets || []).map(ds => ({
                                    label: ds.label || '',
                                    data: ds.data || [],
                                    backgroundColor: ds.backgroundColor || ds.color || '#2563EB',
                                    borderColor: ds.borderColor || ds.color || '#2563EB',
                                    borderWidth: chartType === 'line' ? 2 : 0,
                                    tension: chartType === 'line' ? 0.3 : 0,
                                    fill: chartType === 'line',
                                })),
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: (chartType === 'pie' || chartType === 'doughnut') ? 'bottom' : 'top',
                                    },
                                },
                                scales: (chartType === 'pie' || chartType === 'doughnut') ? {} : {
                                    y: { beginAtZero: true },
                                },
                            },
                        };

                        this.chart = new Chart(ctx, config);
                    },
                }));
            });
        </script>
    @endif

</x-layouts.app>
