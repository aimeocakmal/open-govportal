@php
    $themes = config('themes.valid_themes', ['default' => 'Default']);
@endphp

@if (count($themes) > 1)
<div
    x-data="{
        theme: document.documentElement.dataset.theme || 'default',
        set(name) {
            this.theme = name;
            document.documentElement.dataset.theme = name;
            document.cookie = 'govportal_theme=' + name + ';path=/;max-age=31536000;SameSite=Lax';
        }
    }"
    class="flex items-center gap-1"
    role="group"
    aria-label="{{ app()->getLocale() === 'ms' ? 'Pilih tema' : 'Choose theme' }}"
>
    @foreach ($themes as $key => $label)
        <button
            @click="set('{{ $key }}')"
            :class="{ 'ring-2 ring-primary text-primary': theme === '{{ $key }}' }"
            class="px-2 py-1 rounded text-xs font-medium border border-border text-muted
                   hover:text-primary hover:border-primary transition-colors"
            aria-label="{{ $label }}"
        >
            {{ $label }}
        </button>
    @endforeach
</div>
@endif
