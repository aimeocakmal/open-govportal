{{-- Recursive desktop submenu (flyout right, flips left if no space) --}}
@props(['items', 'locale', 'labelKey', 'level' => 2])

@foreach ($items as $child)
    @if (!empty($child['children']))
        <div x-data="{
                 subOpen: false,
                 flipLeft: false,
                 checkFlip() {
                     const panel = this.$refs.panel;
                     if (!panel) return;
                     const rect = panel.getBoundingClientRect();
                     this.flipLeft = rect.right > window.innerWidth - 16;
                 }
             }"
             class="relative"
             @mouseenter="subOpen = true; $nextTick(() => checkFlip())"
             @mouseleave="subOpen = false">
            @if ($child['url'])
                <a href="/{{ $locale }}/{{ $child['url'] }}"
                   class="flex items-center justify-between gap-2 px-4 py-2 text-body-sm text-text-secondary hover:bg-bg-washed hover:text-primary transition-colors duration-short">
                    <span>{{ $child[$labelKey] ?? $child['label_ms'] }}</span>
                    <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <button type="button"
                        @click="subOpen = !subOpen; $nextTick(() => checkFlip())"
                        class="flex items-center justify-between gap-2 w-full px-4 py-2 text-body-sm text-text-secondary hover:bg-bg-washed hover:text-primary transition-colors duration-short">
                    <span>{{ $child[$labelKey] ?? $child['label_ms'] }}</span>
                    <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            @endif

            <div x-ref="panel"
                 x-show="subOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-short"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-short"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute top-0 w-56 rounded-md bg-white border border-border shadow-context-menu py-1"
                 :style="'z-index: {{ 50 + $level * 10 }};' + (flipLeft ? 'right: 100%; margin-right: -4px;' : 'left: 100%; margin-left: -4px;')">
                <x-layout.nav-desktop-submenu :items="$child['children']" :locale="$locale" :label-key="$labelKey" :level="$level + 1" />
            </div>
        </div>
    @else
        <a href="/{{ $locale }}/{{ $child['url'] }}"
           class="block px-4 py-2 text-body-sm text-text-secondary hover:bg-bg-washed hover:text-primary transition-colors duration-short">
            {{ $child[$labelKey] ?? $child['label_ms'] }}
        </a>
    @endif
@endforeach
