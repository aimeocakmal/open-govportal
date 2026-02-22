@props(['links'])

@php
    $locale = app()->getLocale();
@endphp

@if($links->isNotEmpty())
    <section class="py-12 sm:py-16 bg-surface" aria-labelledby="quick-links-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="quick-links-heading" class="text-2xl sm:text-3xl font-bold text-text mb-8 text-center">
                {{ __('home.quick_links.title') }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($links as $link)
                    <a
                        href="{{ $link->url }}"
                        target="{{ str_starts_with($link->url, 'http') ? '_blank' : '_self' }}"
                        rel="{{ str_starts_with($link->url, 'http') ? 'noopener noreferrer' : '' }}"
                        class="group flex items-center gap-4 p-4 sm:p-5 bg-white rounded-xl border border-border hover:border-primary hover:shadow-md transition-all duration-200"
                    >
                        {{-- Icon --}}
                        @if($link->icon)
                            <div class="shrink-0 w-14 h-14 rounded-lg bg-info-light flex items-center justify-center">
                                <img
                                    src="{{ $link->icon }}"
                                    alt=""
                                    class="w-8 h-8 object-contain"
                                    aria-hidden="true"
                                >
                            </div>
                        @else
                            <div class="shrink-0 w-14 h-14 rounded-lg bg-info-light flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                        @endif

                        {{-- Label --}}
                        <div class="flex-1 min-w-0">
                            <span class="font-semibold text-text group-hover:text-primary transition-colors line-clamp-1">
                                {{ $link->{'label_' . $locale} ?? $link->label_ms }}
                            </span>
                        </div>

                        {{-- Arrow --}}
                        <svg class="w-5 h-5 text-muted group-hover:text-primary group-hover:translate-x-1 transition-all shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
