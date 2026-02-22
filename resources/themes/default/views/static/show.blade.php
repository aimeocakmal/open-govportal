<x-layouts.app :title="$page->{'title_' . app()->getLocale()} ?? $page->title_ms">

    @php
        $locale = app()->getLocale();
        $title = $page->{"title_{$locale}"} ?? $page->title_ms;
        $content = $page->{"content_{$locale}"} ?? $page->content_ms;
    @endphp

    @push('seo')
        @if($page->{"meta_title_{$locale}"})
            <meta property="og:title" content="{{ $page->{"meta_title_{$locale}"} }}">
        @endif
        @if($page->{"meta_desc_{$locale}"})
            <meta name="description" content="{{ $page->{"meta_desc_{$locale}"} }}">
            <meta property="og:description" content="{{ $page->{"meta_desc_{$locale}"} }}">
        @endif
    @endpush

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => $title, 'url' => ''],
    ]" />

    <section class="py-10 sm:py-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-heading-md font-semibold text-text mb-8">
                {{ $title }}
            </h1>

            <div class="prose prose-lg text-muted max-w-none">
                {!! $content !!}
            </div>
        </div>
    </section>

</x-layouts.app>
