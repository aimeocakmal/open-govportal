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

    {{-- Changelog --}}
    @if (count($changelog) > 0)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament.settings.platform_version.changelog') }}
            </x-slot>

            <div class="space-y-4">
                @foreach ($changelog as $section)
                    @php
                        $typeKey = 'filament.settings.platform_version.type_' . $section['type'];
                        $color = match ($section['type']) {
                            'added' => 'success',
                            'changed' => 'info',
                            'fixed' => 'warning',
                            'removed' => 'danger',
                            default => 'gray',
                        };
                    @endphp

                    <div>
                        <x-filament::badge :color="$color">
                            {{ __($typeKey) }}
                        </x-filament::badge>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-gray-300">
                            @foreach ($section['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
