<x-layouts.app :title="__('common.nav.home')">

    {{-- Hero placeholder (replaced in Phase 3 / Week 6) --}}
    <section class="bg-primary py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-white leading-tight">
                {{ __('common.site_name') }}
            </h1>
            <p class="mt-4 text-lg text-primary-light">
                {{ __('common.site_tagline') }}
            </p>
        </div>
    </section>

    {{-- Content placeholder --}}
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-muted">
            <p>{{ app()->getLocale() === 'ms' ? 'Kandungan laman utama akan dipaparkan di sini (Fasa 3).' : 'Homepage content will appear here (Phase 3).' }}</p>
        </div>
    </section>

</x-layouts.app>
