<x-filament-panels::page>
    {{-- Current Version --}}
    <x-filament::section>
        <x-slot name="heading">
            {{ __('filament.settings.platform_version.current_version') }}
        </x-slot>
        <x-slot name="description">
            {{ __('filament.settings.platform_version.current_version_desc') }}
        </x-slot>

        <div class="flex items-center gap-3">
            <x-filament::badge size="lg" color="primary">
                v{{ $version }}
            </x-filament::badge>

            @if ($releasedAt)
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('filament.settings.platform_version.released') }}
                    {{ \Carbon\Carbon::parse($releasedAt)->translatedFormat('d F Y') }}
                </span>
            @endif
        </div>
    </x-filament::section>

    {{-- Current Version Changelog --}}
    @if (count($changelog) > 0)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament.settings.platform_version.changelog') }}
            </x-slot>

            @include('filament.pages.partials.changelog-sections', ['sections' => $changelog])
        </x-filament::section>
    @endif

    {{-- Version History --}}
    @if (count($history) > 0)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament.settings.platform_version.version_history') }}
            </x-slot>

            <div class="space-y-10">
                @foreach ($history as $release)
                    <div x-data="{ expanded: false }">
                        <div class="flex items-center gap-3">
                            <x-filament::badge size="lg" color="gray">
                                v{{ $release['version'] }}
                            </x-filament::badge>

                            @if ($release['released_at'] ?? false)
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('filament.settings.platform_version.released') }}
                                    {{ \Carbon\Carbon::parse($release['released_at'])->translatedFormat('d F Y') }}
                                </span>
                            @endif
                        </div>

                        @if (count($release['changelog'] ?? []) > 0)
                            <div class="mt-6 relative">
                                <div :style="!expanded && 'max-height: 6rem; overflow: hidden'">
                                    @include('filament.pages.partials.changelog-sections', ['sections' => $release['changelog']])
                                </div>

                                {{-- Fade overlay when collapsed --}}
                                <div
                                    x-show="!expanded"
                                    class="absolute bottom-0 left-0 right-0 h-10 bg-gradient-to-t from-white dark:from-gray-900 pointer-events-none"
                                ></div>

                                <button
                                    type="button"
                                    @click="expanded = !expanded"
                                    class="mt-1 font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300" style="font-size: 0.75rem;"
                                >
                                    <span x-text="expanded ? '{{ __('filament.settings.platform_version.show_less') }}' : '{{ __('filament.settings.platform_version.read_more') }}'"></span>
                                </button>
                            </div>
                        @endif
                    </div>

                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
