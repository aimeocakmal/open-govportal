<x-layouts.app :title="__('home.title')">

    @php
        $locale = app()->getLocale();
    @endphp

    @foreach($sectionOrder as $section)
        @switch($section)
            @case('hero_banner')
                @if($showHeroBanner)
                    <x-home.hero-banner :banners="$heroBanners" />
                @endif
                @break

            @case('quick_links')
                @if($showQuickLinks)
                    <x-home.quick-links :links="$quickLinks" />
                @endif
                @break

            @case('broadcasts')
                @if($showBroadcasts && $broadcasts->isNotEmpty())
                    <section class="py-12 sm:py-16" aria-labelledby="broadcasts-heading">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            {{-- Section header --}}
                            <div class="flex items-center justify-between mb-8">
                                <h2 id="broadcasts-heading" class="text-2xl sm:text-3xl font-bold text-text">
                                    {{ __('home.broadcasts.title') }}
                                </h2>
                                <a
                                    href="/{{ $locale }}/siaran"
                                    class="hidden sm:inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark transition-colors"
                                >
                                    {{ __('home.broadcasts.view_all') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>

                            {{-- Cards grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($broadcasts as $broadcast)
                                    <x-home.broadcast-card :broadcast="$broadcast" />
                                @endforeach
                            </div>

                            {{-- Mobile view all --}}
                            <div class="mt-8 text-center sm:hidden">
                                <a
                                    href="/{{ $locale }}/siaran"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark transition-colors"
                                >
                                    {{ __('home.broadcasts.view_all') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </section>
                @endif
                @break

            @case('achievements')
                @if($showAchievements && $achievements->isNotEmpty())
                    <section class="py-12 sm:py-16 bg-surface" aria-labelledby="achievements-heading">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
                                {{-- Left: heading (sticky on desktop) --}}
                                <div class="lg:sticky lg:top-24 lg:self-start">
                                    <h2 id="achievements-heading" class="text-2xl sm:text-3xl font-bold text-text">
                                        {{ __('home.achievements.title') }}
                                    </h2>
                                    <p class="mt-2 text-muted">
                                        {{ __('home.achievements.description') }}
                                    </p>
                                    <a
                                        href="/{{ $locale }}/pencapaian"
                                        class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark transition-colors"
                                    >
                                        {{ __('home.achievements.view_all') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>

                                {{-- Right: timeline --}}
                                <div class="lg:col-span-2">
                                    @foreach($achievements as $achievement)
                                        <x-home.achievement-card
                                            :achievement="$achievement"
                                            :isLast="$loop->last"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                @break
        @endswitch
    @endforeach

</x-layouts.app>
