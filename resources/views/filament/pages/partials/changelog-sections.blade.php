<div class="space-y-4">
    @foreach ($sections as $section)
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

            <ul class="mt-2 space-y-1 text-gray-700 dark:text-gray-300" style="list-style-type: disc; list-style-position: inside; font-size: 0.8rem;">
                @foreach ($section['items'] as $item)
                    <li style="display: list-item;">{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
