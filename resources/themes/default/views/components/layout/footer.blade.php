@php
    $locale    = app()->getLocale();
    $labelKey  = 'label_' . $locale;
    $footerData = $footerData ?? [];
    $currentYear = date('Y');
@endphp

<footer class="bg-gray-900 text-gray-400 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Branding --}}
            <div class="space-y-3">
                <p class="font-heading font-semibold text-white text-heading-2xs leading-snug">
                    {{ __('common.site_name') }}
                </p>
                <p class="text-body-sm text-gray-500 leading-relaxed">
                    {{ __('common.site_tagline') }}
                </p>

                {{-- Social links --}}
                <div class="flex items-center gap-3 pt-1">
                    @foreach (['facebook_url' => 'Facebook', 'twitter_url' => 'X', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube'] as $key => $label)
                        @php $url = $footerData[$key] ?? '' @endphp
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="text-gray-500 hover:text-white transition-colors duration-short text-body-xs">
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <h3 class="text-body-sm font-semibold text-white uppercase tracking-wider mb-4">
                    {{ __('common.footer.quick_links') }}
                </h3>
                <ul class="space-y-2">
                    @foreach ([
                        ['label_ms' => 'Siaran', 'label_en' => 'Broadcasts', 'url' => 'siaran'],
                        ['label_ms' => 'Pencapaian', 'label_en' => 'Achievements', 'url' => 'pencapaian'],
                        ['label_ms' => 'Direktori', 'label_en' => 'Directory', 'url' => 'direktori'],
                        ['label_ms' => 'Dasar', 'label_en' => 'Policy', 'url' => 'dasar'],
                    ] as $link)
                        <li>
                            <a href="/{{ $locale }}/{{ $link['url'] }}"
                               class="text-body-sm text-gray-500 hover:text-white transition-colors duration-short">
                                {{ $link[$labelKey] ?? $link['label_ms'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Legal links --}}
            <div>
                <h3 class="text-body-sm font-semibold text-white uppercase tracking-wider mb-4">
                    {{ __('common.footer.more_info') }}
                </h3>
                <ul class="space-y-2">
                    @foreach ([
                        ['label_ms' => 'Profil Kementerian', 'label_en' => 'Ministry Profile', 'url' => 'profil-kementerian'],
                        ['label_ms' => 'Hubungi Kami', 'label_en' => 'Contact Us', 'url' => 'hubungi-kami'],
                        ['label_ms' => 'Penafian', 'label_en' => 'Disclaimer', 'url' => 'penafian'],
                        ['label_ms' => 'Dasar Privasi', 'label_en' => 'Privacy Policy', 'url' => 'dasar-privasi'],
                    ] as $link)
                        <li>
                            <a href="/{{ $locale }}/{{ $link['url'] }}"
                               class="text-body-sm text-gray-500 hover:text-white transition-colors duration-short">
                                {{ $link[$labelKey] ?? $link['label_ms'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-10 pt-6 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-body-xs text-gray-600">
                &copy; {{ $currentYear }}
                {{ __('common.site_name') }}. {{ __('common.footer.copyright') }}
            </p>
            <p class="text-body-xs text-gray-600">
                {{ __('common.footer.built_by') }}
                <span class="text-gray-500">{{ __('common.site_name') }}</span>
            </p>
        </div>
    </div>
</footer>
