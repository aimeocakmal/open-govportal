{{-- MyDS Masthead — Official government website identification bar --}}
<div
    x-data="{ open: false }"
    class="bg-bg-washed border-b border-border-light print:hidden"
    data-nosnippet
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header row --}}
        <button
            @click="open = !open"
            type="button"
            class="flex w-full items-center justify-between py-2 text-body-xs text-text-secondary"
            :aria-expanded="open"
        >
            <span class="flex items-center gap-2">
                {{-- Malaysia flag icon --}}
                <svg width="32" height="16" viewBox="0 0 32 16" fill="none" aria-hidden="true" class="shrink-0">
                    <rect width="32" height="16" rx="1" fill="#FFF"/>
                    <clipPath id="flag-clip"><rect width="32" height="16" rx="1"/></clipPath>
                    <g clip-path="url(#flag-clip)">
                        {{-- Red and white stripes --}}
                        <rect y="0" width="32" height="1.143" fill="#D10525"/>
                        <rect y="2.286" width="32" height="1.143" fill="#D10525"/>
                        <rect y="4.571" width="32" height="1.143" fill="#D10525"/>
                        <rect y="6.857" width="32" height="1.143" fill="#D10525"/>
                        <rect y="9.143" width="32" height="1.143" fill="#D10525"/>
                        <rect y="11.429" width="32" height="1.143" fill="#D10525"/>
                        <rect y="13.714" width="32" height="1.143" fill="#D10525"/>
                        {{-- Blue canton --}}
                        <rect width="16" height="9.143" fill="#102A7E"/>
                        {{-- Crescent and star --}}
                        <circle cx="7" cy="4.571" r="2.5" fill="#FAD209"/>
                        <circle cx="7.8" cy="4.571" r="2" fill="#102A7E"/>
                        <polygon points="11,2.5 11.4,3.7 12.6,3.7 11.6,4.4 12,5.6 11,4.9 10,5.6 10.4,4.4 9.4,3.7 10.6,3.7" fill="#FAD209"/>
                    </g>
                </svg>
                <span class="font-medium">{{ __('common.masthead.official') }}</span>
            </span>

            <span class="flex items-center gap-1 text-primary-600 hover:text-primary-700">
                <span class="hidden sm:inline">{{ __('common.masthead.how_to_identify') }}</span>
                <svg
                    class="size-4 transition-transform duration-short"
                    :class="open && 'rotate-180'"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </span>
        </button>

        {{-- Expandable content --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-short"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-short"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="pb-4"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-3 border-t border-border-light">
                {{-- .gov.my explanation --}}
                <div class="flex gap-3">
                    {{-- Government building icon --}}
                    <div class="shrink-0 mt-0.5">
                        <svg class="size-5 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 20 20" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2l7 4v2H3V6l7-4zM4 8v6M8 8v6M12 8v6M16 8v6M3 14h14M2 17h16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-body-sm font-semibold text-text mb-1">
                            {{ __('common.masthead.govmy_title') }}
                        </p>
                        <p class="text-body-xs text-gray-700">
                            {{ __('common.masthead.govmy_desc_before') }}<strong>{{ __('common.masthead.govmy_domain') }}</strong>{{ __('common.masthead.govmy_desc_after') }}
                        </p>
                    </div>
                </div>

                {{-- HTTPS explanation --}}
                <div class="flex gap-3">
                    {{-- Lock icon --}}
                    <div class="shrink-0 mt-0.5">
                        <svg class="size-5 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 20 20" aria-hidden="true">
                            <rect x="4" y="9" width="12" height="8" rx="1.5"/>
                            <path d="M7 9V6a3 3 0 016 0v3"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-body-sm font-semibold text-text mb-1">
                            {{ __('common.masthead.secure_title') }}
                        </p>
                        <p class="text-body-xs text-gray-700">
                            {{ __('common.masthead.secure_desc_before') }}<svg class="inline size-3 text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><rect x="4" y="9" width="12" height="8" rx="1.5"/><path d="M7 9V6a3 3 0 016 0v3"/></svg>{{ __('common.masthead.secure_desc_or') }}<strong>{{ __('common.masthead.secure_https') }}</strong>{{ __('common.masthead.secure_desc_after') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
