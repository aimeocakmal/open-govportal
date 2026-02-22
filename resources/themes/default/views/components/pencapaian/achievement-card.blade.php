@props(['achievement'])

@php
    $locale = app()->getLocale();
    $title = $achievement->{'title_' . $locale} ?? $achievement->title_ms;
    $description = $achievement->{'description_' . $locale} ?? $achievement->description_ms;
    $url = '/' . $locale . '/pencapaian/' . $achievement->slug;
@endphp

<article class="group bg-white rounded-lg border border-border-light hover:border-primary hover:shadow-card transition-all duration-short overflow-hidden flex flex-col p-5">
    {{-- Icon + featured badge --}}
    <div class="flex items-start justify-between mb-3">
        @if($achievement->icon)
            <img
                src="{{ $achievement->icon }}"
                alt=""
                class="size-10 object-contain"
                aria-hidden="true"
            >
        @else
            <div class="size-10 rounded-full bg-primary-50 flex items-center justify-center" aria-hidden="true">
                <svg class="size-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        @endif

        @if($achievement->is_featured)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-body-xs font-medium bg-primary-50 text-primary-700">
                {{ __('pencapaian.featured') }}
            </span>
        @endif
    </div>

    {{-- Date --}}
    @if($achievement->date)
        <time
            datetime="{{ $achievement->date->toDateString() }}"
            class="text-body-xs text-muted uppercase tracking-wide mb-2"
        >
            {{ $achievement->date->translatedFormat('d F Y') }}
        </time>
    @endif

    {{-- Title --}}
    <h3 class="font-heading font-semibold text-text group-hover:text-primary transition-colors duration-short line-clamp-2 mb-2">
        <a href="{{ $url }}">{{ $title }}</a>
    </h3>

    {{-- Description --}}
    @if($description)
        <p class="text-body-sm text-muted line-clamp-3 flex-1">
            {{ strip_tags($description) }}
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
</article>
