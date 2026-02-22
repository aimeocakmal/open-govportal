@php
    $themes = app(\App\Services\ThemeService::class)->getThemeOptions(app()->getLocale());
@endphp

@if (count($themes) > 1)
<div
    x-data="{
        theme: document.documentElement.dataset.theme || 'default',
        set(name) {
            this.theme = name;
            document.cookie = 'govportal_theme=' + name + ';path=/;max-age=31536000;SameSite=Lax';
            window.location.reload();
        }
    }"
    class="flex items-center gap-1"
    role="group"
    aria-label="{{ __('common.theme.choose') }}"
>
    @foreach ($themes as $key => $label)
        <button
            @click="set('{{ $key }}')"
            :class="{ 'ring-2 ring-fr-primary text-primary': theme === '{{ $key }}' }"
            class="px-2 py-1 rounded-md text-body-xs font-medium border border-border-light text-muted
                   hover:text-primary hover:border-primary transition-colors duration-short"
            aria-label="{{ $label }}"
        >
            {{ $label }}
        </button>
    @endforeach
</div>
@endif
