@props(['staff'])

@php
    $locale = app()->getLocale();
    $position = $staff->{'position_' . $locale} ?? $staff->position_ms;
    $department = $staff->{'department_' . $locale} ?? $staff->department_ms;
    $division = $staff->{'division_' . $locale} ?? $staff->division_ms;
@endphp

<article class="bg-white rounded-lg border border-border-light hover:border-primary hover:shadow-card transition-all duration-short p-5 flex flex-col items-center text-center">
    {{-- Photo --}}
    <div class="mb-4 size-20 rounded-full overflow-hidden bg-bg-washed flex items-center justify-center shrink-0">
        @if($staff->photo)
            <img
                src="{{ $staff->photo }}"
                alt="{{ $staff->name }}"
                class="w-full h-full object-cover"
                loading="lazy"
            >
        @else
            <svg class="size-10 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
        @endif
    </div>

    {{-- Name --}}
    <h3 class="font-heading font-semibold text-text text-body-md mb-1">
        {{ $staff->name }}
    </h3>

    {{-- Position --}}
    @if($position)
        <p class="text-body-sm text-primary font-medium mb-1">{{ $position }}</p>
    @endif

    {{-- Department --}}
    @if($department)
        <p class="text-body-xs text-muted mb-3">{{ $department }}</p>
    @endif

    {{-- Contact details --}}
    <div class="mt-auto w-full space-y-1.5 pt-3 border-t border-border-light text-left">
        @if($staff->email)
            <a href="mailto:{{ $staff->email }}" class="flex items-center gap-2 text-body-xs text-muted hover:text-primary transition-colors duration-short">
                <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
                <span class="truncate">{{ $staff->email }}</span>
            </a>
        @endif

        @if($staff->phone)
            <a href="tel:{{ $staff->phone }}" class="flex items-center gap-2 text-body-xs text-muted hover:text-primary transition-colors duration-short">
                <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                </svg>
                <span>{{ $staff->phone }}</span>
            </a>
        @endif
    </div>
</article>
