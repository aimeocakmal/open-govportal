@php
    $locale    = app()->getLocale();
    $labelKey  = 'label_' . $locale;
    $footerData = $footerData ?? [];
    $currentYear = date('Y');
@endphp

<footer class="bg-gray-800 text-gray-300 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Branding --}}
            <div class="space-y-3">
                <p class="font-bold text-white text-base leading-snug">
                    {{ app()->getLocale() === 'ms'
                        ? 'Kementerian Digital Malaysia'
                        : 'Ministry of Digital Malaysia' }}
                </p>
                <p class="text-sm text-gray-400 leading-relaxed">
                    {{ app()->getLocale() === 'ms'
                        ? 'Memimpin transformasi digital negara.'
                        : 'Leading the nation\'s digital transformation.' }}
                </p>

                {{-- Social links placeholder --}}
                <div class="flex items-center gap-3 pt-1">
                    @foreach (['facebook_url' => 'Facebook', 'twitter_url' => 'X', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube'] as $key => $label)
                        @php $url = $footerData[$key] ?? '' @endphp
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="text-gray-400 hover:text-white transition-colors text-xs">
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                    {{ $locale === 'ms' ? 'Pautan Cepat' : 'Quick Links' }}
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
                               class="text-sm text-gray-400 hover:text-white transition-colors">
                                {{ $link[$labelKey] ?? $link['label_ms'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Legal links --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                    {{ $locale === 'ms' ? 'Maklumat Lanjut' : 'More Info' }}
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
                               class="text-sm text-gray-400 hover:text-white transition-colors">
                                {{ $link[$labelKey] ?? $link['label_ms'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-10 pt-6 border-t border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-500">
                &copy; {{ $currentYear }}
                {{ $locale === 'ms' ? 'Kementerian Digital Malaysia. Hak cipta terpelihara.' : 'Ministry of Digital Malaysia. All rights reserved.' }}
            </p>
            <p class="text-xs text-gray-600">
                {{ $locale === 'ms' ? 'Dibangunkan oleh' : 'Built by' }}
                <span class="text-gray-500">Kementerian Digital Malaysia</span>
            </p>
        </div>
    </div>
</footer>
