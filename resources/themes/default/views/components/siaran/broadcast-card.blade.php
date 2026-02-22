@props(['broadcast'])

@php
    $locale = app()->getLocale();
    $title = $broadcast->{'title_' . $locale} ?? $broadcast->title_ms;
    $excerpt = $broadcast->{'excerpt_' . $locale} ?? $broadcast->excerpt_ms;
    $url = '/' . $locale . '/siaran/' . $broadcast->slug;

    $typeBadgeColors = [
        'press_release' => 'bg-primary-50 text-primary-700',
        'announcement' => 'bg-warning-50 text-warning-700',
        'news' => 'bg-success-50 text-success-700',
    ];
    $badgeClass = $typeBadgeColors[$broadcast->type] ?? 'bg-gray-100 text-muted';
@endphp

<article class="group bg-white rounded-lg border border-border-light hover:border-primary hover:shadow-card transition-all duration-short overflow-hidden flex flex-col">
    {{-- Featured image --}}
    @if($broadcast->featured_image)
        <a href="{{ $url }}" class="block aspect-[16/9] overflow-hidden">
            <img
                src="{{ $broadcast->featured_image }}"
                alt="{{ $title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-medium"
                loading="lazy"
            >
        </a>
    @endif

    <div class="p-5 flex flex-col flex-1">
        {{-- Type badge + date --}}
        <div class="flex items-center gap-3 mb-3">
            @if($broadcast->type)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-body-xs font-medium {{ $badgeClass }}">
                    {{ __('siaran.filter.' . $broadcast->type) }}
                </span>
            @endif

            @if($broadcast->published_at)
                <time
                    datetime="{{ $broadcast->published_at->toDateString() }}"
                    class="text-body-xs text-muted"
                >
                    {{ $broadcast->published_at->translatedFormat('d M Y') }}
                </time>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="font-heading font-semibold text-text group-hover:text-primary transition-colors duration-short line-clamp-2 mb-2">
            <a href="{{ $url }}">{{ $title }}</a>
        </h3>

        {{-- Excerpt --}}
        @if($excerpt)
            <p class="text-body-sm text-muted line-clamp-3 flex-1">
                {{ $excerpt }}
            </p>
        @endif

        {{-- Read more --}}
        <a
            href="{{ $url }}"
            class="mt-4 inline-flex items-center gap-1 text-body-sm font-medium text-primary hover:text-primary-dark transition-colors duration-short"
        >
            {{ __('common.actions.read_more') }}
            <svg class="size-4 group-hover:translate-x-0.5 transition-transform duration-short" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</article>
