<x-layouts.app :title="__('hubungi.title')">

    @php
        $locale = app()->getLocale();
    @endphp

    <x-layout.breadcrumb :items="[
        ['label' => __('common.nav.home'), 'url' => '/' . $locale],
        ['label' => __('hubungi.title'), 'url' => ''],
    ]" />

    <section class="py-10 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page header --}}
            <div class="mb-10">
                <h1 class="font-heading text-heading-md font-semibold text-text">
                    {{ __('hubungi.title') }}
                </h1>
                <p class="mt-2 text-body-md text-muted max-w-2xl">
                    {{ __('hubungi.description') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                {{-- Addresses --}}
                <div>
                    <h2 class="font-heading text-heading-xs font-semibold text-text mb-6">
                        {{ __('hubungi.addresses_title') }}
                    </h2>

                    @if($addresses->isEmpty())
                        <p class="text-body-md text-muted">—</p>
                    @else
                        <div class="space-y-6">
                            @foreach($addresses as $address)
                                <x-hubungi.address-card :address="$address" />
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Contact form --}}
                <div>
                    <h2 class="font-heading text-heading-xs font-semibold text-text mb-6">
                        {{ __('hubungi.form.title') }}
                    </h2>
                    <livewire:contact-form />
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
