@php
    $locale = app()->getLocale();
    $title = $broadcast->{'title_' . $locale} ?? $broadcast->title_ms;
    $content = $broadcast->{'content_' . $locale} ?? $broadcast->content_ms;
    $excerpt = $broadcast->{'excerpt_' . $locale} ?? $broadcast->excerpt_ms;

    $typeBadgeColors = [
        'press_release' => 'bg-primary-50 text-primary-700',
        'announcement' => 'bg-warning-50 text-warning-700',
        'news' => 'bg-success-50 text-success-700',
    ];
    $badgeClass = $typeBadgeColors[$broadcast->type] ?? 'bg-gray-100 text-muted';
@endphp

<x-layouts.app :title="$title">

    @push('seo')
        <meta name="description" content="{{ Str::limit(strip_tags($excerpt ?? $content ?? ''), 160) }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ Str::limit(strip_tags($excerpt ?? $content ?? ''), 160) }}">
        <meta property="og:type" content="article">
        <meta property="og:url" content="{{ url()->current() }}">
        @if($broadcast->featured_image)
            <meta property="og:image" content="{{ $broadcast->featured_image }}">
            <meta name="twitter:card" content="summary_large_image">
        @else
            <meta name="twitter:card" content="summary">
        @endif
        @if($broadcast->published_at)
            <meta property="article:published_time" content="{{ $broadcast->published_at->toIso8601String() }}">
        @endif
    @endpush

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('siaran.title'), 'url' => '/' . $locale . '/siaran'],
        ['label' => $title, 'url' => ''],
    ]" />

    <article class="py-10 sm:py-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <header class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    @if($broadcast->type)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-body-xs font-medium {{ $badgeClass }}">
                            {{ __('siaran.filter.' . $broadcast->type) }}
                        </span>
                    @endif

                    @if($broadcast->published_at)
                        <time
                            datetime="{{ $broadcast->published_at->toDateString() }}"
                            class="text-body-sm text-muted"
                        >
                            {{ $broadcast->published_at->translatedFormat('d F Y') }}
                        </time>
                    @endif
                </div>

                <h1 class="font-heading text-heading-lg font-semibold text-text leading-tight">
                    {{ $title }}
                </h1>
            </header>

            {{-- Featured image --}}
            @if($broadcast->featured_image)
                <figure class="mb-8 rounded-lg overflow-hidden">
                    <img
                        src="{{ $broadcast->featured_image }}"
                        alt="{{ $title }}"
                        class="w-full h-auto object-cover"
                    >
                </figure>
            @endif

            {{-- Content --}}
            <div class="prose prose-lg max-w-none text-text">
                {!! $content !!}
            </div>
        </div>
    </article>

    {{-- Related broadcasts --}}
    @if($related->isNotEmpty())
        <section class="py-10 sm:py-14 bg-bg-washed border-t border-border-light" aria-labelledby="related-heading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 id="related-heading" class="font-heading text-heading-sm font-semibold text-text mb-8">
                    {{ __('siaran.related') }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($related as $item)
                        <x-siaran.broadcast-card :broadcast="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
