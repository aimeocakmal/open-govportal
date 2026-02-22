<x-layouts.app :title="__('profil.title')">

    @php
        $locale = app()->getLocale();
    @endphp

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('profil.title'), 'url' => ''],
    ]" />

    <section class="py-10 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page header --}}
            <div class="mb-10">
                <h1 class="font-heading text-heading-md font-semibold text-text">
                    {{ __('profil.title') }}
                </h1>
                <p class="mt-2 text-body-md text-muted max-w-2xl">
                    {{ __('profil.description') }}
                </p>
            </div>

            {{-- Minister profile --}}
            @if($minister)
                <x-profil.minister-card :minister="$minister" />
            @else
                <div class="rounded-lg border border-border-light bg-bg-washed px-6 py-12 text-center mb-10">
                    <p class="text-body-md text-muted">{{ __('profil.no_minister') }}</p>
                </div>
            @endif

            {{-- Vision & Mission --}}
            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
                @if($vision)
                    <div class="rounded-lg border border-border bg-white p-6">
                        <h2 class="font-heading text-heading-xs font-semibold text-text mb-4">
                            {{ __('profil.vision') }}
                        </h2>
                        <div class="prose prose-sm text-muted max-w-none">
                            {!! $vision !!}
                        </div>
                    </div>
                @endif

                @if($mission)
                    <div class="rounded-lg border border-border bg-white p-6">
                        <h2 class="font-heading text-heading-xs font-semibold text-text mb-4">
                            {{ __('profil.mission') }}
                        </h2>
                        <div class="prose prose-sm text-muted max-w-none">
                            {!! $mission !!}
                        </div>
                    </div>
                @endif
            </div>

            {{-- About section --}}
            @if($about)
                <div class="mt-12 rounded-lg border border-border bg-white p-6">
                    <h2 class="font-heading text-heading-xs font-semibold text-text mb-4">
                        {{ __('profil.about') }}
                    </h2>
                    <div class="prose prose-sm text-muted max-w-none">
                        {!! $about !!}
                    </div>
                </div>
            @endif
        </div>
    </section>

</x-layouts.app>
