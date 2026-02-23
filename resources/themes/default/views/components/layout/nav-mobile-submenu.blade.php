{{-- Recursive mobile accordion submenu --}}
@props(['items', 'locale', 'labelKey', 'depth' => 1])

@foreach ($items as $child)
    @if (!empty($child['children']))
        <div x-data="{ subOpen: false }">
            <button type="button"
                    @click="subOpen = !subOpen"
                    class="flex items-center justify-between w-full px-3 py-2 rounded-md text-body-sm text-text-secondary hover:bg-bg-washed hover:text-primary transition-colors duration-short">
                <span>{{ $child[$labelKey] ?? $child['label_ms'] }}</span>
                <svg class="size-4 transition-transform duration-short" :class="subOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="subOpen" x-cloak x-collapse class="ml-4 flex flex-col gap-1">
                <x-layout.nav-mobile-submenu :items="$child['children']" :locale="$locale" :label-key="$labelKey" :depth="$depth + 1" />
            </div>
        </div>
    @else
        <a href="/{{ $locale }}/{{ $child['url'] }}"
           @click="$dispatch('close-mobile-menu')"
           class="block px-3 py-2 rounded-md text-body-sm text-text-secondary hover:bg-bg-washed hover:text-primary transition-colors duration-short">
            {{ $child[$labelKey] ?? $child['label_ms'] }}
        </a>
    @endif
@endforeach
