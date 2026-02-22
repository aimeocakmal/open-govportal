@props(['address'])

@php
    $locale = app()->getLocale();
    $label = $address->{"label_{$locale}"} ?? $address->label_ms;
    $addressText = $address->{"address_{$locale}"} ?? $address->address_ms;
@endphp

<div class="rounded-lg border border-border bg-white p-5">
    <h3 class="font-heading text-body-md font-semibold text-text">{{ $label }}</h3>

    <p class="mt-2 text-body-sm text-muted whitespace-pre-line">{{ $addressText }}</p>

    <div class="mt-3 space-y-1 text-body-sm">
        @if($address->phone)
            <div class="flex items-center gap-2 text-muted">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>{{ __('hubungi.phone') }}: {{ $address->phone }}</span>
            </div>
        @endif

        @if($address->fax)
            <div class="flex items-center gap-2 text-muted">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                </svg>
                <span>{{ __('hubungi.fax') }}: {{ $address->fax }}</span>
            </div>
        @endif

        @if($address->email)
            <div class="flex items-center gap-2 text-muted">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <a href="mailto:{{ $address->email }}" class="text-primary hover:underline">{{ $address->email }}</a>
            </div>
        @endif
    </div>

    @if($address->google_maps_url)
        <div class="mt-3">
            <a
                href="{{ $address->google_maps_url }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-body-xs font-medium text-primary hover:underline"
            >
                {{ __('hubungi.view_map') }}
                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    @endif
</div>
