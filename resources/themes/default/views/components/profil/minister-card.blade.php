@props(['minister'])

@php
    $locale = app()->getLocale();
    $title = $minister->{"title_{$locale}"} ?? $minister->title_ms;
    $bio = $minister->{"bio_{$locale}"} ?? $minister->bio_ms;
@endphp

<div class="rounded-lg border border-border bg-white overflow-hidden">
    <div class="flex flex-col md:flex-row">
        {{-- Photo --}}
        @if($minister->photo)
            <div class="md:w-72 flex-shrink-0">
                <img
                    src="{{ $minister->photo }}"
                    alt="{{ $minister->name }}"
                    class="w-full h-64 md:h-full object-cover"
                >
            </div>
        @endif

        {{-- Details --}}
        <div class="flex-1 p-6 md:p-8">
            <span class="text-body-xs font-medium uppercase tracking-wider text-primary">
                {{ __('profil.minister') }}
            </span>
            <h2 class="mt-2 font-heading text-heading-sm font-semibold text-text">
                {{ $minister->name }}
            </h2>
            <p class="mt-1 text-body-md text-muted">{{ $title }}</p>

            @if($minister->appointed_at)
                <p class="mt-3 text-body-xs text-muted">
                    {{ __('profil.appointed') }}: {{ $minister->appointed_at->translatedFormat('d F Y') }}
                </p>
            @endif

            @if($bio)
                <div class="mt-4 prose prose-sm text-muted max-w-none">
                    {!! $bio !!}
                </div>
            @endif
        </div>
    </div>
</div>
