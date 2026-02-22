@props(['items' => []])

@if(count($items) > 0)
<nav aria-label="{{ __('common.breadcrumb') }}" class="bg-bg-washed border-b border-border-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center gap-2 py-3 text-body-sm overflow-x-auto">
            @foreach($items as $item)
                <li class="flex items-center gap-2 whitespace-nowrap">
                    @if(!$loop->first)
                        <svg class="size-4 text-muted shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif

                    @if(!$loop->last)
                        <a href="{{ $item['url'] }}" class="text-muted hover:text-primary transition-colors duration-short">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-text font-medium" aria-current="page">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
@endif
