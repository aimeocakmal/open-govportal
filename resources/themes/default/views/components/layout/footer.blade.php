@php
    $locale           = app()->getLocale();
    $labelKey         = 'label_' . $locale;
    $footerMenuItems  = $footerMenuItems ?? collect();
    $footerSocialLinks = $footerSocialLinks ?? collect();
    $footerData       = $footerData ?? [];
    $currentYear      = date('Y');
@endphp

<footer class="bg-gray-900 text-gray-400 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Branding + Social (from FooterSetting) --}}
            <div class="space-y-3">
                <p class="font-heading font-semibold text-white text-heading-2xs leading-snug">
                    {{ __('common.site_name') }}
                </p>
                <p class="text-body-sm text-gray-500 leading-relaxed">
                    {{ __('common.site_tagline') }}
                </p>

                {{-- Social links from footer_settings --}}
                @if ($footerSocialLinks->isNotEmpty())
                    <div class="flex items-center gap-3 pt-1">
                        @foreach ($footerSocialLinks as $social)
                            @if ($social->url)
                                <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-gray-500 hover:text-white transition-colors duration-short text-body-xs">
                                    {{ $social->$labelKey ?? $social->label_ms }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    {{-- Fallback to settings table social URLs --}}
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
                @endif
            </div>

            {{-- Right: Dynamic columns from public_footer menu --}}
            <div class="lg:col-span-2 flex flex-col sm:flex-row gap-8">
                @foreach ($footerMenuItems as $group)
                    <div class="flex-1">
                        <h3 class="text-body-sm font-semibold text-white uppercase tracking-wider mb-4">
                            {{ $group->$labelKey ?? $group->label_ms }}
                        </h3>
                        <ul class="space-y-2">
                            @foreach ($group->children as $link)
                                @php
                                    $isExternal = str_starts_with($link->url ?? '', 'http');
                                    $href = $isExternal ? $link->url : '/' . $locale . $link->url;
                                @endphp
                                <li>
                                    <a href="{{ $href }}"
                                       @if ($isExternal || $link->target === '_blank') target="_blank" rel="noopener noreferrer" @endif
                                       class="text-body-sm text-gray-500 hover:text-white transition-colors duration-short">
                                        {{ $link->$labelKey ?? $link->label_ms }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-10 pt-6 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-body-xs text-gray-600">
                &copy; {{ $currentYear }}
                {{ __('common.site_name') }}. {{ __('common.footer.copyright') }}
            </p>
            <div class="flex items-center gap-4 text-body-xs text-gray-600">
                <a href="/{{ $locale }}/penafian" class="hover:text-gray-400 transition-colors duration-short">
                    {{ __('common.footer.penafian') }}
                </a>
                <a href="/{{ $locale }}/dasar-privasi" class="hover:text-gray-400 transition-colors duration-short">
                    {{ __('common.footer.dasar_privasi') }}
                </a>
            </div>
        </div>
    </div>
</footer>
