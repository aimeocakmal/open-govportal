@props(['policy'])

@php
    $locale = app()->getLocale();
    $title = $policy->{"title_{$locale}"} ?? $policy->title_ms;
    $description = $policy->{"description_{$locale}"} ?? $policy->description_ms;
@endphp

<article class="rounded-lg border border-border bg-white p-6 transition-shadow duration-short hover:shadow-md">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="mb-2">
                <span class="inline-block rounded-full bg-primary-50 px-3 py-0.5 text-body-xs font-medium text-primary">
                    {{ __("dasar.categories.{$policy->category}") }}
                </span>
            </div>

            <h3 class="font-heading text-heading-xs font-semibold text-text">
                {{ $title }}
            </h3>

            @if($description)
                <p class="mt-2 text-body-sm text-muted line-clamp-2">
                    {{ Str::limit(strip_tags($description), 200) }}
                </p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-4 text-body-xs text-muted">
                @if($policy->published_at)
                    <span>{{ __('dasar.published_at') }}: {{ $policy->published_at->translatedFormat('d M Y') }}</span>
                @endif
                @if($policy->file_size)
                    <span>{{ __('dasar.file_size') }}: {{ number_format($policy->file_size / 1024 / 1024, 1) }} MB</span>
                @endif
                @if($policy->download_count > 0)
                    <span>{{ number_format($policy->download_count) }} {{ __('dasar.downloads') }}</span>
                @endif
            </div>
        </div>

        @if($policy->file_url)
            <div class="flex-shrink-0">
                <a
                    href="{{ route('dasar.download', ['locale' => $locale, 'id' => $policy->id]) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-body-sm font-medium text-white
                           hover:bg-primary-dark transition-colors duration-short"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    {{ __('dasar.download') }}
                </a>
            </div>
        @endif
    </div>
</article>
