<x-layouts.app :title="__('errors.403.title')">

    <section class="py-20 sm:py-32">
        <div class="max-w-lg mx-auto px-4 text-center">
            <p class="font-heading text-7xl font-bold text-yellow-500">403</p>
            <h1 class="mt-4 font-heading text-heading-md font-semibold text-text">
                {{ __('errors.403.title') }}
            </h1>
            <p class="mt-4 text-body-md text-muted">
                {{ __('errors.403.message') }}
            </p>
            <div class="mt-8">
                <a
                    href="/{{ app()->getLocale() }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-body-sm font-medium text-white
                           hover:bg-primary-dark transition-colors duration-short"
                >
                    {{ __('errors.back_home') }}
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>
