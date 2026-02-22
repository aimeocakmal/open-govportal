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

            <div class="space-y-6">
                @foreach ($history as $release)
                    <div>
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
                            <div class="mt-3">
                                @include('filament.pages.partials.changelog-sections', ['sections' => $release['changelog']])
                            </div>
                        @endif
                    </div>

                    @if (! $loop->last)
                        <hr class="border-gray-200 dark:border-gray-700">
                    @endif
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
