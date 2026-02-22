@props(['achievement', 'isLast' => false])

@php
    $locale = app()->getLocale();
    $title = $achievement->{'title_' . $locale} ?? $achievement->title_ms;
    $description = $achievement->{'description_' . $locale} ?? $achievement->description_ms;
    $url = '/' . $locale . '/pencapaian/' . $achievement->slug;
@endphp

<div class="relative pl-8 {{ !$isLast ? 'pb-10' : '' }}">
    {{-- Timeline line --}}
    @if(!$isLast)
        <div class="absolute left-[11px] top-6 bottom-0 w-px bg-border" aria-hidden="true"></div>
    @endif

    {{-- Timeline dot --}}
    <div class="absolute left-0 top-1.5 w-6 h-6 rounded-full border-2 border-primary bg-white flex items-center justify-center" aria-hidden="true">
        @if($achievement->is_featured)
            <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
        @endif
    </div>

    {{-- Content --}}
    <div class="group">
        {{-- Date --}}
        @if($achievement->date)
            <time
                datetime="{{ $achievement->date->toDateString() }}"
                class="text-xs font-medium text-muted uppercase tracking-wide"
            >
                {{ $achievement->date->translatedFormat('d F Y') }}
            </time>
        @endif

        {{-- Title --}}
        <h3 class="mt-1 font-semibold text-text group-hover:text-primary transition-colors">
            <a href="{{ $url }}">{{ $title }}</a>
        </h3>

        {{-- Description --}}
        @if($description)
            <p class="mt-1 text-sm text-muted line-clamp-3">
                {{ strip_tags($description) }}
            </p>
        @endif
    </div>
</div>
