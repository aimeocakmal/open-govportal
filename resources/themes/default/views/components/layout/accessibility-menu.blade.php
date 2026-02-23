{{-- Accessibility Menu — slide-in settings panel (triggered from nav header) --}}
<div
    x-data="accessibilityMenu"
    @keydown.window.ctrl.u.prevent="toggle()"
    @toggle-accessibility.window="toggle()"
    x-cloak
>
    {{-- Settings panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-out duration-150"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        @click.away="open = false"
        id="a11y-panel"
        role="dialog"
        aria-label="{{ __('accessibility.title') }}"
        class="fixed top-0 right-0 z-50 flex h-full w-80 flex-col bg-bg border-l border-border shadow-context-menu overflow-y-auto"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-border px-4 py-3">
            <h2 class="text-body-lg font-semibold text-text">{{ __('accessibility.title') }}</h2>
            <button
                @click="open = false"
                class="flex h-8 w-8 items-center justify-center rounded-md text-muted hover:text-text
                       transition-colors duration-short"
                aria-label="{{ __('common.close') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="flex-1 space-y-6 px-4 py-4">

            {{-- Font Size --}}
            <fieldset>
                <legend class="mb-2 text-body-sm font-semibold text-text">{{ __('accessibility.font_size') }}</legend>
                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('accessibility.font_size') }}">
                    <button
                        @click="setFontSize('default')"
                        :class="fontSize === 'default' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="fontSize === 'default'"
                    >{{ __('accessibility.default') }} (100%)</button>
                    <button
                        @click="setFontSize('small')"
                        :class="fontSize === 'small' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="fontSize === 'small'"
                    >{{ __('accessibility.small') }} (80%)</button>
                    <button
                        @click="setFontSize('large')"
                        :class="fontSize === 'large' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="fontSize === 'large'"
                    >{{ __('accessibility.large') }} (120%)</button>
                    <button
                        @click="setFontSize('xlarge')"
                        :class="fontSize === 'xlarge' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="fontSize === 'xlarge'"
                    >{{ __('accessibility.extra_large') }} (140%)</button>
                </div>
            </fieldset>

            {{-- Font Type --}}
            <fieldset>
                <legend class="mb-2 text-body-sm font-semibold text-text">{{ __('accessibility.font_type') }}</legend>
                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('accessibility.font_type') }}">
                    <button
                        @click="setFontType('default')"
                        :class="fontType === 'default' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="fontType === 'default'"
                    >{{ __('accessibility.default') }}</button>
                    <button
                        @click="setFontType('arial')"
                        :class="fontType === 'arial' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        style="font-family: Arial, sans-serif"
                        role="radio"
                        :aria-checked="fontType === 'arial'"
                    >Arial <span class="text-body-xs text-text-secondary">{{ __('accessibility.sans_serif') }}</span></button>
                    <button
                        @click="setFontType('times')"
                        :class="fontType === 'times' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        style="font-family: 'Times New Roman', serif"
                        role="radio"
                        :aria-checked="fontType === 'times'"
                    >Times New Roman <span class="text-body-xs text-text-secondary">{{ __('accessibility.serif') }}</span></button>
                    <button
                        @click="setFontType('courier')"
                        :class="fontType === 'courier' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        style="font-family: 'Courier New', monospace"
                        role="radio"
                        :aria-checked="fontType === 'courier'"
                    >Courier New <span class="text-body-xs text-text-secondary">{{ __('accessibility.monospaced') }}</span></button>
                </div>
            </fieldset>

            {{-- Background Color --}}
            <fieldset>
                <legend class="mb-2 text-body-sm font-semibold text-text">{{ __('accessibility.bg_color') }}</legend>
                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('accessibility.bg_color') }}">
                    <button
                        @click="setBgColor('default')"
                        :class="bgColor === 'default' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="bgColor === 'default'"
                    >{{ __('accessibility.default') }}</button>
                    <button
                        @click="setBgColor('white')"
                        :class="bgColor === 'white' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="bgColor === 'white'"
                    ><span class="inline-block h-4 w-4 rounded-full border border-gray-300" style="background:#ffffff"></span> {{ __('accessibility.white') }}</button>
                    <button
                        @click="setBgColor('yellow')"
                        :class="bgColor === 'yellow' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="bgColor === 'yellow'"
                    ><span class="inline-block h-4 w-4 rounded-full border border-gray-300" style="background:#ffffcc"></span> {{ __('accessibility.yellow') }}</button>
                    <button
                        @click="setBgColor('blue')"
                        :class="bgColor === 'blue' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="bgColor === 'blue'"
                    ><span class="inline-block h-4 w-4 rounded-full border border-gray-300" style="background:#ccf2ff"></span> {{ __('accessibility.blue') }}</button>
                </div>
            </fieldset>

            {{-- Contrast --}}
            <fieldset>
                <legend class="mb-2 text-body-sm font-semibold text-text">{{ __('accessibility.contrast') }}</legend>
                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('accessibility.contrast') }}">
                    <button
                        @click="setContrast('default')"
                        :class="contrast === 'default' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="contrast === 'default'"
                    >{{ __('accessibility.auto') }}</button>
                    <button
                        @click="setContrast('light')"
                        :class="contrast === 'light' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="contrast === 'light'"
                    >{{ __('accessibility.light_mode') }}</button>
                    <button
                        @click="setContrast('dark')"
                        :class="contrast === 'dark' ? 'ring-2 ring-fr-primary text-primary border-primary' : 'text-muted border-border-light'"
                        class="rounded-md border px-3 py-1.5 text-body-sm font-medium transition-colors duration-short hover:text-primary hover:border-primary"
                        role="radio"
                        :aria-checked="contrast === 'dark'"
                    >{{ __('accessibility.dark_mode') }}</button>
                </div>
            </fieldset>
        </div>

        {{-- Footer — Reset button --}}
        <div class="border-t border-border px-4 py-3">
            <button
                @click="reset()"
                class="w-full rounded-md border border-border px-4 py-2 text-body-sm font-medium text-muted
                       hover:text-text hover:border-text transition-colors duration-short"
            >{{ __('accessibility.reset') }}</button>
        </div>
    </div>

    {{-- Backdrop overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/30"
        aria-hidden="true"
    ></div>
</div>
