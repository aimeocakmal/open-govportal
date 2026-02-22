<div>
    @if($submitted)
        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-body-sm text-green-800">{{ __('hubungi.form.success') }}</p>
        </div>
    @elseif($rateLimited)
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
            <p class="text-body-sm text-yellow-800">{{ __('hubungi.form.rate_limited') }}</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5">
            {{-- Name --}}
            <div>
                <label for="contact-name" class="block text-body-sm font-medium text-text mb-1">
                    {{ __('hubungi.form.name') }}
                </label>
                <input
                    id="contact-name"
                    type="text"
                    wire:model.blur="name"
                    placeholder="{{ __('hubungi.form.name_placeholder') }}"
                    class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-body-sm text-text
                           placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                           transition-colors duration-short"
                >
                @error('name')
                    <p class="mt-1 text-body-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="contact-email" class="block text-body-sm font-medium text-text mb-1">
                    {{ __('hubungi.form.email') }}
                </label>
                <input
                    id="contact-email"
                    type="email"
                    wire:model.blur="email"
                    placeholder="{{ __('hubungi.form.email_placeholder') }}"
                    class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-body-sm text-text
                           placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                           transition-colors duration-short"
                >
                @error('email')
                    <p class="mt-1 text-body-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Subject --}}
            <div>
                <label for="contact-subject" class="block text-body-sm font-medium text-text mb-1">
                    {{ __('hubungi.form.subject') }}
                </label>
                <input
                    id="contact-subject"
                    type="text"
                    wire:model.blur="subject"
                    placeholder="{{ __('hubungi.form.subject_placeholder') }}"
                    class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-body-sm text-text
                           placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                           transition-colors duration-short"
                >
                @error('subject')
                    <p class="mt-1 text-body-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message --}}
            <div>
                <label for="contact-message" class="block text-body-sm font-medium text-text mb-1">
                    {{ __('hubungi.form.message') }}
                </label>
                <textarea
                    id="contact-message"
                    wire:model.blur="message"
                    rows="5"
                    placeholder="{{ __('hubungi.form.message_placeholder') }}"
                    class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-body-sm text-text
                           placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                           transition-colors duration-short resize-y"
                ></textarea>
                @error('message')
                    <p class="mt-1 text-body-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-body-sm font-medium text-white
                           hover:bg-primary-dark transition-colors duration-short disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submit">{{ __('hubungi.form.submit') }}</span>
                    <span wire:loading wire:target="submit">
                        <svg class="animate-spin size-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
