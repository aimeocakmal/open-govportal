<x-layouts.app :title="__('pencapaian.title')">

    @php
        $locale = app()->getLocale();
    @endphp

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('pencapaian.title'), 'url' => ''],
    ]" />

    <section class="py-10 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page header --}}
            <div class="mb-8">
                <h1 class="font-heading text-heading-md font-semibold text-text">
                    {{ __('pencapaian.title') }}
                </h1>
                <p class="mt-2 text-body-md text-muted max-w-2xl">
                    {{ __('pencapaian.description') }}
                </p>
            </div>

            <livewire:pencapaian-list />
        </div>
    </section>

</x-layouts.app>
