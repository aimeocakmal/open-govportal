@php
    $locale = app()->getLocale();
    $title = $achievement->{'title_' . $locale} ?? $achievement->title_ms;
    $description = $achievement->{'description_' . $locale} ?? $achievement->description_ms;
@endphp

<x-layouts.app :title="$title">

    @push('seo')
        <meta name="description" content="{{ Str::limit(strip_tags($description ?? ''), 160) }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ Str::limit(strip_tags($description ?? ''), 160) }}">
        <meta property="og:type" content="article">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:card" content="summary">
    @endpush

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('pencapaian.title'), 'url' => '/' . $locale . '/pencapaian'],
        ['label' => $title, 'url' => ''],
    ]" />

    <article class="py-10 sm:py-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <header class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    @if($achievement->is_featured)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-body-xs font-medium bg-primary-50 text-primary-700">
                            {{ __('pencapaian.featured') }}
                        </span>
                    @endif

                    @if($achievement->date)
                        <time
                            datetime="{{ $achievement->date->toDateString() }}"
                            class="text-body-sm text-muted"
                        >
                            {{ $achievement->date->translatedFormat('d F Y') }}
                        </time>
                    @endif
                </div>

                <h1 class="font-heading text-heading-lg font-semibold text-text leading-tight">
                    {{ $title }}
                </h1>
            </header>

            {{-- Icon --}}
            @if($achievement->icon)
                <div class="mb-8 flex items-center justify-center">
                    <img
                        src="{{ $achievement->icon }}"
                        alt=""
                        class="size-24 object-contain"
                        aria-hidden="true"
                    >
                </div>
            @endif

            {{-- Content --}}
            <div class="prose prose-lg max-w-none text-text">
                {!! $description !!}
            </div>
        </div>
    </article>

</x-layouts.app>
