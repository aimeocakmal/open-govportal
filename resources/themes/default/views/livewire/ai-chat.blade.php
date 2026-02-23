<div
    x-data="{
        open: false,
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        scrollToBottom() {
            const el = this.$refs.messagesContainer;
            if (el) el.scrollTop = el.scrollHeight;
        }
    }"
    x-on:ai-chat-scroll-bottom.window="$nextTick(() => scrollToBottom())"
    class="fixed bottom-0 right-0 z-50"
>
    {{-- Floating Chat Button --}}
    <button
        x-show="!open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        @click="toggle()"
        class="fixed bottom-6 right-6 flex items-center gap-2 rounded-full bg-primary px-5 py-3 text-white shadow-lg
               hover:bg-primary-dark transition-colors duration-short focus:outline-none focus:ring-2 focus:ring-primary-200 focus:ring-offset-2"
        aria-label="{{ __('ai.chat_title') }}"
    >
        {{-- Chat icon --}}
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.671 1.09-.085 2.17-.207 3.238-.364 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
        </svg>
        <span class="text-body-sm font-medium">{{ $botName }}</span>
    </button>

    {{-- Chat Window --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 flex flex-col w-[22rem] sm:w-96 h-[32rem] rounded-2xl bg-white border border-border shadow-xl overflow-hidden"
        x-cloak
    >
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3 px-4 py-3 bg-primary text-white border-b border-primary-dark">
            <div class="flex items-center gap-3 min-w-0">
                @if($botAvatar)
                    <img src="{{ $botAvatar }}" alt="{{ $botName }}" class="size-8 rounded-full object-cover ring-2 ring-white/30 shrink-0">
                @else
                    <div class="size-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                        </svg>
                    </div>
                @endif
                <span class="text-body-sm font-semibold truncate">{{ $botName }}</span>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                {{-- New Chat --}}
                <button
                    wire:click="clearChat"
                    class="p-1.5 rounded-lg hover:bg-white/20 transition-colors"
                    title="{{ __('ai.new_chat') }}"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                    </svg>
                </button>
                {{-- Close --}}
                <button
                    @click="toggle()"
                    class="p-1.5 rounded-lg hover:bg-white/20 transition-colors"
                    title="{{ __('ai.close') }}"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Disclaimer Modal --}}
        @if(!$disclaimerAccepted)
            <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                <div class="size-12 rounded-full bg-primary-50 flex items-center justify-center mb-4">
                    <svg class="size-6 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <p class="text-body-sm text-muted mb-6 leading-relaxed">{{ $disclaimer }}</p>
                <div class="flex gap-3">
                    <button
                        wire:click="acceptDisclaimer"
                        class="rounded-lg bg-primary px-5 py-2 text-body-sm font-medium text-white hover:bg-primary-dark transition-colors"
                    >
                        {{ __('ai.disclaimer_accept') }}
                    </button>
                    <button
                        @click="toggle()"
                        class="rounded-lg border border-border px-5 py-2 text-body-sm font-medium text-muted hover:bg-bg transition-colors"
                    >
                        {{ __('ai.disclaimer_decline') }}
                    </button>
                </div>
            </div>
        @else
            {{-- Messages Area --}}
            <div
                x-ref="messagesContainer"
                class="flex-1 overflow-y-auto p-4 space-y-4"
            >
                {{-- Welcome Message --}}
                <div class="flex gap-2.5">
                    <div class="size-7 rounded-full bg-primary-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                    <div class="rounded-2xl rounded-tl-sm bg-bg px-4 py-2.5 max-w-[85%]">
                        <p class="text-body-sm text-text leading-relaxed">{{ $welcomeMessage }}</p>
                    </div>
                </div>

                {{-- Conversation Messages --}}
                @foreach($messages as $msg)
                    @if($msg['role'] === 'user')
                        <div class="flex justify-end">
                            <div class="rounded-2xl rounded-tr-sm bg-primary px-4 py-2.5 max-w-[85%]">
                                <p class="text-body-sm text-white leading-relaxed whitespace-pre-wrap">{{ $msg['content'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex gap-2.5">
                            <div class="size-7 rounded-full bg-primary-50 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                </svg>
                            </div>
                            <div class="rounded-2xl rounded-tl-sm bg-bg px-4 py-2.5 max-w-[85%]">
                                <p class="text-body-sm text-text leading-relaxed whitespace-pre-wrap">{{ $msg['content'] }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach

                {{-- Thinking Indicator --}}
                @if($isThinking)
                    <div class="flex gap-2.5">
                        <div class="size-7 rounded-full bg-primary-50 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                        </div>
                        <div class="rounded-2xl rounded-tl-sm bg-bg px-4 py-3 max-w-[85%]">
                            <div class="flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-muted animate-bounce [animation-delay:0ms]"></span>
                                <span class="size-2 rounded-full bg-muted animate-bounce [animation-delay:150ms]"></span>
                                <span class="size-2 rounded-full bg-muted animate-bounce [animation-delay:300ms]"></span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Rate Limited --}}
                @if($rateLimited)
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-2.5">
                        <p class="text-body-xs text-yellow-800">{{ __('ai.rate_limited') }}</p>
                    </div>
                @endif

                {{-- Error --}}
                @if($hasError)
                    <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2.5">
                        <p class="text-body-xs text-red-800">{{ __('ai.error') }}</p>
                    </div>
                @endif
            </div>

            {{-- Language Toggle --}}
            @if($showLanguageToggle)
                <div class="flex items-center justify-center gap-1 px-4 py-1.5 border-t border-border">
                    <button
                        wire:click="setLanguage('ms')"
                        class="rounded-md px-3 py-1 text-body-xs font-medium transition-colors
                               {{ ($preferredLanguage === 'ms' || ($preferredLanguage === '' && app()->getLocale() === 'ms')) ? 'bg-primary text-white' : 'text-muted hover:bg-bg' }}"
                    >
                        {{ __('ai.language_toggle_ms') }}
                    </button>
                    <button
                        wire:click="setLanguage('en')"
                        class="rounded-md px-3 py-1 text-body-xs font-medium transition-colors
                               {{ ($preferredLanguage === 'en' || ($preferredLanguage === '' && app()->getLocale() === 'en')) ? 'bg-primary text-white' : 'text-muted hover:bg-bg' }}"
                    >
                        {{ __('ai.language_toggle_en') }}
                    </button>
                </div>
            @endif

            {{-- Input Area --}}
            <div class="border-t border-border p-3">
                <form wire:submit="send" class="flex items-end gap-2">
                    <div class="flex-1">
                        <textarea
                            wire:model="input"
                            rows="1"
                            placeholder="{{ $placeholder }}"
                            class="w-full resize-none rounded-xl border border-border bg-bg px-4 py-2.5 text-body-sm text-text
                                   placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                                   transition-colors duration-short"
                            @keydown.enter.prevent="if(!$event.shiftKey) $wire.send()"
                            @disabled($isThinking)
                        ></textarea>
                        @error('input')
                            <p class="mt-1 text-body-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button
                        type="submit"
                        class="shrink-0 rounded-xl bg-primary p-2.5 text-white hover:bg-primary-dark transition-colors
                               disabled:opacity-50 disabled:cursor-not-allowed"
                        @disabled($isThinking)
                        title="{{ __('ai.send') }}"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" wire:loading.remove wire:target="send">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        <svg class="size-5 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="send">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </form>
                <p class="mt-1.5 text-center text-body-xs text-muted">{{ __('ai.powered_by') }}</p>
            </div>
        @endif
    </div>
</div>
