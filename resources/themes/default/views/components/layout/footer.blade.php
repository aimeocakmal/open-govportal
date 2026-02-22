@php
    $locale           = app()->getLocale();
    $labelKey         = 'label_' . $locale;
    $footerMenuItems  = $footerMenuItems ?? collect();
    $footerBranding   = $footerBranding ?? collect();
    $footerSocialLinks = $footerSocialLinks ?? collect();
    $currentYear      = date('Y');
@endphp

<footer class="bg-bg-washed text-text-secondary mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Dynamic branding block + Social icons --}}
            <div class="space-y-3">
                @foreach ($footerBranding as $item)
                    @if ($item->type === 'logo' && $item->url)
                        <img src="{{ $item->url }}" alt="{{ $item->$labelKey ?? $item->label_ms }}" class="h-10">
                    @elseif ($item->type === 'heading')
                        <p class="font-heading font-semibold text-text text-heading-2xs leading-snug">
                            {{ $item->$labelKey ?? $item->label_ms }}
                        </p>
                    @elseif ($item->type === 'text')
                        <p class="text-body-sm text-muted leading-relaxed">
                            {!! nl2br(e($item->$labelKey ?? $item->label_ms)) !!}
                        </p>
                    @elseif ($item->type === 'subheading')
                        <p class="text-body-sm font-semibold text-text pt-2">
                            {{ $item->$labelKey ?? $item->label_ms }}
                        </p>
                    @endif
                @endforeach

                {{-- Social links with icons --}}
                @if ($footerSocialLinks->isNotEmpty())
                    <div class="flex items-center gap-3 pt-1">
                        @foreach ($footerSocialLinks as $social)
                            @if ($social->url)
                                <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-muted hover:text-text transition-colors duration-short"
                                   title="{{ $social->$labelKey ?? $social->label_ms }}">
                                    @if ($social->icon)
                                        <x-icons.social :name="$social->icon" class="size-5" />
                                    @else
                                        <span class="text-body-xs">{{ $social->$labelKey ?? $social->label_ms }}</span>
                                    @endif
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
                        <h3 class="text-body-sm font-semibold text-text uppercase tracking-wider mb-4">
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
                                       class="text-body-sm text-muted hover:text-text transition-colors duration-short">
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
        <div class="mt-10 pt-6 border-t border-border-light flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-body-xs text-muted">
                &copy; {{ $currentYear }}
                {{ __('common.site_name') }}. {{ __('common.footer.copyright') }}
            </p>
            <div class="flex items-center gap-4 text-body-xs text-muted">
                <a href="/{{ $locale }}/penafian" class="hover:text-text transition-colors duration-short">
                    {{ __('common.footer.penafian') }}
                </a>
                <a href="/{{ $locale }}/dasar-privasi" class="hover:text-text transition-colors duration-short">
                    {{ __('common.footer.dasar_privasi') }}
                </a>
            </div>
        </div>
    </div>
</footer>
