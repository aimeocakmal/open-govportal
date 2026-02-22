<x-layouts.app :title="__('dasar.title')">

    @php
        $locale = app()->getLocale();
    @endphp

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('dasar.title'), 'url' => ''],
    ]" />

    <section class="py-10 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page header --}}
            <div class="mb-8">
                <h1 class="font-heading text-heading-md font-semibold text-text">
                    {{ __('dasar.title') }}
                </h1>
                <p class="mt-2 text-body-md text-muted max-w-2xl">
                    {{ __('dasar.description') }}
                </p>
            </div>

            {{-- Category filter (Alpine.js) --}}
            <div x-data="{ category: 'all' }" class="space-y-6">
                <div class="flex flex-wrap gap-2">
                    @foreach(['all', 'keselamatan', 'data', 'digital', 'ict', 'perkhidmatan'] as $cat)
                        <button
                            x-on:click="category = '{{ $cat }}'"
                            :class="category === '{{ $cat }}'
                                ? 'bg-primary text-white'
                                : 'bg-bg-washed text-text hover:bg-bg-washed-dark'"
                            class="rounded-full px-4 py-1.5 text-body-sm font-medium transition-colors duration-short"
                        >
                            {{ __("dasar.categories.{$cat}") }}
                        </button>
                    @endforeach
                </div>

                {{-- Policy list --}}
                @if($policies->isEmpty())
                    <div class="rounded-lg border border-border-light bg-bg-washed px-6 py-12 text-center">
                        <p class="text-body-md text-muted">{{ __('dasar.no_results') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($policies as $policy)
                            <div
                                x-show="category === 'all' || category === '{{ $policy->category }}'"
                                x-transition
                            >
                                <x-dasar.policy-card :policy="$policy" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

</x-layouts.app>
