@php
    $locale   = app()->getLocale();
    $labelKey = 'label_' . $locale;
    $navItems = $navItems ?? [];

    // Language switcher: swap the locale segment in the current URL path
    $currentPath = request()->path(); // e.g. "ms/siaran"
    $msUrl = '/' . preg_replace('#^(ms|en)(/|$)#', 'ms$2', $currentPath);
    $enUrl = '/' . preg_replace('#^(ms|en)(/|$)#', 'en$2', $currentPath);
@endphp

<header
    x-data="{ open: false }"
    @close-mobile-menu.window="open = false"
    class="bg-white border-b border-border shadow-button sticky top-0 z-50"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="/{{ $locale }}" class="flex items-center gap-3 shrink-0">
                <span class="font-heading font-semibold text-heading-2xs text-primary leading-tight">
                    {{ config('app.name') }}
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-6" aria-label="{{ __('common.nav.home') }}">
                @foreach ($navItems as $item)
                    @if (!empty($item['children']))
                        {{-- Dropdown for items with children --}}
                        <div x-data="{ dropdownOpen: false }" class="relative"
                             @mouseenter="dropdownOpen = true"
                             @mouseleave="dropdownOpen = false">
                            @if ($item['url'])
                                <a href="/{{ $locale }}/{{ $item['url'] }}"
                                   class="inline-flex items-center gap-1 text-body-sm font-medium text-text-secondary hover:text-primary transition-colors duration-short">
                                    {{ $item[$labelKey] ?? $item['label_ms'] }}
                                    <svg class="size-4 transition-transform duration-short" :class="dropdownOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                            @else
                                <button type="button"
                                        @click="dropdownOpen = !dropdownOpen"
                                        class="inline-flex items-center gap-1 text-body-sm font-medium text-text-secondary hover:text-primary transition-colors duration-short">
                                    {{ $item[$labelKey] ?? $item['label_ms'] }}
                                    <svg class="size-4 transition-transform duration-short" :class="dropdownOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            @endif

                            <div x-show="dropdownOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-short"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-short"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-1"
                                 class="absolute left-0 top-full mt-1 w-56 rounded-md bg-white border border-border shadow-context-menu py-1 z-50 overflow-visible">
                                <x-layout.nav-desktop-submenu :items="$item['children']" :locale="$locale" :label-key="$labelKey" />
                            </div>
                        </div>
                    @else
                        {{-- Simple link --}}
                        <a
                            href="/{{ $locale }}/{{ $item['url'] }}"
                            class="text-body-sm font-medium text-text-secondary hover:text-primary transition-colors duration-short
                                   {{ request()->is($locale . '/' . $item['url'] . '*') ? 'text-primary font-semibold' : '' }}"
                        >
                            {{ $item[$labelKey] ?? $item['label_ms'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            {{-- Right side: language switcher + accessibility + theme switcher --}}
            <div class="hidden md:flex items-center gap-4">
                {{-- Language switcher --}}
                <div class="flex items-center gap-1 text-body-sm font-medium">
                    <a
                        href="{{ $msUrl }}"
                        class="{{ $locale === 'ms' ? 'text-primary font-semibold' : 'text-muted hover:text-primary' }} transition-colors duration-short"
                        hreflang="ms"
                        aria-label="{{ __('common.language.ms') }}"
                    >BM</a>
                    <span class="text-border">|</span>
                    <a
                        href="{{ $enUrl }}"
                        class="{{ $locale === 'en' ? 'text-primary font-semibold' : 'text-muted hover:text-primary' }} transition-colors duration-short"
                        hreflang="en"
                        aria-label="{{ __('common.language.en') }}"
                    >EN</a>
                </div>

                {{-- Accessibility --}}
                <button
                    @click="$dispatch('toggle-accessibility')"
                    aria-controls="a11y-panel"
                    class="p-1.5 rounded-md text-muted hover:text-primary hover:bg-bg-washed transition-colors duration-short"
                    aria-label="{{ __('accessibility.open') }}"
                    title="{{ __('accessibility.title') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="4.5" r="2" fill="currentColor" stroke="none" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 0l-3 6m3-6l3 6M7 10h10" />
                    </svg>
                </button>

                <x-layout.theme-switcher />
            </div>

            {{-- Mobile hamburger --}}
            <button
                @click="open = !open"
                type="button"
                class="md:hidden p-2 rounded-md text-muted hover:text-primary hover:bg-bg-washed transition-colors duration-short"
                :aria-expanded="open"
                aria-controls="mobile-menu"
                aria-label="Toggle navigation"
            >
                <svg x-show="!open" class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div
        id="mobile-menu"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-short"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-short"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="md:hidden border-t border-border bg-white"
    >
        <nav class="px-4 py-3 flex flex-col gap-1" aria-label="Mobile navigation">
            @foreach ($navItems as $item)
                @if (!empty($item['children']))
                    {{-- Collapsible group for items with children --}}
                    <div x-data="{ subOpen: false }">
                        <button type="button"
                                @click="subOpen = !subOpen"
                                class="flex items-center justify-between w-full px-3 py-2 rounded-md text-body-sm font-medium text-gray-700 hover:bg-bg-washed hover:text-primary transition-colors duration-short">
                            <span>{{ $item[$labelKey] ?? $item['label_ms'] }}</span>
                            <svg class="size-4 transition-transform duration-short" :class="subOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="subOpen" x-cloak x-collapse class="ml-4 flex flex-col gap-1">
                            <x-layout.nav-mobile-submenu :items="$item['children']" :locale="$locale" :label-key="$labelKey" />
                        </div>
                    </div>
                @else
                    <a
                        href="/{{ $locale }}/{{ $item['url'] }}"
                        @click="open = false"
                        class="block px-3 py-2 rounded-md text-body-sm font-medium text-gray-700 hover:bg-bg-washed hover:text-primary transition-colors duration-short
                               {{ request()->is($locale . '/' . $item['url'] . '*') ? 'bg-primary-50 text-primary font-semibold' : '' }}"
                    >
                        {{ $item[$labelKey] ?? $item['label_ms'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Mobile language + accessibility + theme --}}
        <div class="px-4 py-3 border-t border-border-light flex items-center gap-4">
            <div class="flex items-center gap-1 text-body-sm font-medium">
                <a href="{{ $msUrl }}" class="{{ $locale === 'ms' ? 'text-primary font-semibold' : 'text-muted' }}">BM</a>
                <span class="text-border">|</span>
                <a href="{{ $enUrl }}" class="{{ $locale === 'en' ? 'text-primary font-semibold' : 'text-muted' }}">EN</a>
            </div>
            <button
                @click="$dispatch('toggle-accessibility')"
                class="p-1.5 rounded-md text-muted hover:text-primary hover:bg-bg-washed transition-colors duration-short"
                aria-label="{{ __('accessibility.open') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="4.5" r="2" fill="currentColor" stroke="none" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 0l-3 6m3-6l3 6M7 10h10" />
                </svg>
            </button>
            <x-layout.theme-switcher />
        </div>
    </div>
</header>
