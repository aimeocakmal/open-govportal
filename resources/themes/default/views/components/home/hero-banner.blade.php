@props(['banners'])

@php
    $locale = app()->getLocale();
@endphp

@if($banners->isNotEmpty())
    <section
        x-data="heroCarousel"
        class="relative bg-primary overflow-hidden"
        aria-label="{{ __('home.hero.default_title') }}"
    >
        {{-- Carousel viewport --}}
        <div x-ref="viewport" class="overflow-hidden">
            <div class="flex">
                @foreach($banners as $banner)
                    <div class="flex-[0_0_100%] min-w-0">
                        <div class="relative h-[400px] sm:h-[480px] lg:h-[560px]">
                            {{-- Background image --}}
                            @if($banner->image)
                                <img
                                    src="{{ $banner->image }}"
                                    alt="{{ $banner->{'image_alt_' . $locale} ?? $banner->{'title_' . $locale} }}"
                                    class="absolute inset-0 w-full h-full object-cover"
                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                >
                                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-transparent"></div>
                            @else
                                {{-- Fallback gradient --}}
                                <div class="absolute inset-0 bg-gradient-to-br from-primary-800 via-primary-600 to-primary-400"></div>
                            @endif

                            {{-- Content --}}
                            <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
                                <div class="max-w-2xl">
                                    @if($banner->{'title_' . $locale})
                                        <h2 class="font-heading text-heading-md sm:text-heading-lg lg:text-heading-xl font-semibold text-white leading-tight">
                                            {{ $banner->{'title_' . $locale} }}
                                        </h2>
                                    @endif

                                    @if($banner->{'subtitle_' . $locale})
                                        <p class="mt-4 text-body-lg sm:text-body-xl text-white/90 leading-relaxed">
                                            {{ $banner->{'subtitle_' . $locale} }}
                                        </p>
                                    @endif

                                    @if($banner->cta_url && $banner->{'cta_label_' . $locale})
                                        <a
                                            href="{{ $banner->cta_url }}"
                                            class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-white text-primary font-semibold rounded-lg shadow-button hover:bg-gray-50 transition-colors duration-short"
                                        >
                                            {{ $banner->{'cta_label_' . $locale} }}
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Navigation arrows --}}
        @if($banners->count() > 1)
            <button
                @click="prev()"
                class="absolute left-4 top-1/2 -translate-y-1/2 size-10 rounded-full bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 transition-colors duration-short flex items-center justify-center"
                aria-label="{{ __('common.pagination.previous') }}"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <button
                @click="next()"
                class="absolute right-4 top-1/2 -translate-y-1/2 size-10 rounded-full bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 transition-colors duration-short flex items-center justify-center"
                aria-label="{{ __('common.pagination.next') }}"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Dot indicators --}}
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2">
                @foreach($banners as $index => $banner)
                    <button
                        @click="goTo({{ $index }})"
                        :class="current === {{ $index }}
                            ? 'bg-white w-8 h-2'
                            : 'bg-white/50 w-2 h-2 hover:bg-white/70'"
                        class="rounded-full transition-all duration-medium"
                        aria-label="Slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>
@else
    {{-- Default hero when no banners exist --}}
    <section class="bg-gradient-to-br from-primary-800 via-primary-600 to-primary-400 py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="font-heading text-heading-lg sm:text-heading-xl font-semibold text-white leading-tight">
                {{ __('home.hero.default_title') }}
            </h1>
            <p class="mt-4 text-body-lg sm:text-body-xl text-white/90">
                {{ __('home.hero.default_subtitle') }}
            </p>
        </div>
    </section>
@endif
