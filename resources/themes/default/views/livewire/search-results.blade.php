<div>
    {{-- Search input --}}
    <div class="mb-8">
        <label for="search-input" class="sr-only">{{ __('carian.label') }}</label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="size-5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                id="search-input"
                type="search"
                wire:model.live.debounce.500ms="query"
                placeholder="{{ __('carian.placeholder') }}"
                class="w-full rounded-lg border border-border bg-white py-3 pl-12 pr-4 text-body-md text-text
                       placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                       transition-colors duration-short"
                autofocus
            >
        </div>
    </div>

    {{-- Results --}}
    @if(mb_strlen(trim($query)) >= 2)
        {{-- Search overrides (promoted results) --}}
        @if($overrides->isNotEmpty())
            <div class="mb-6 space-y-3">
                @foreach($overrides as $override)
                    @php
                        $locale = app()->getLocale();
                        $title = $override->{"title_{$locale}"} ?? $override->title_ms;
                        $desc = $override->{"description_{$locale}"} ?? $override->description_ms;
                    @endphp
                    <a href="{{ $override->url }}" class="block rounded-lg border-2 border-primary-100 bg-primary-50/50 p-4 hover:bg-primary-50 transition-colors duration-short">
                        <h3 class="text-body-md font-semibold text-primary">{{ $title }}</h3>
                        @if($desc)
                            <p class="mt-1 text-body-sm text-muted">{{ Str::limit(strip_tags($desc), 160) }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Regular results --}}
        @if($results->isEmpty() && $overrides->isEmpty())
            <div class="rounded-lg border border-border-light bg-bg-washed px-6 py-12 text-center">
                <p class="text-body-md text-muted">{{ __('carian.no_results') }}</p>
            </div>
        @elseif($results->isNotEmpty())
            <p class="text-body-sm text-muted mb-4">
                {{ __('carian.result_count', ['count' => $results->count()]) }}
            </p>
            <div class="space-y-4">
                @foreach($results as $result)
                    <a href="{{ $result['url'] }}" class="block rounded-lg border border-border bg-white p-4 hover:shadow-md transition-shadow duration-short">
                        <h3 class="text-body-md font-semibold text-text hover:text-primary">
                            {{ $result['title'] ?? __('carian.title') }}
                        </h3>
                        @if($result['excerpt'])
                            <p class="mt-1 text-body-sm text-muted">{{ $result['excerpt'] }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</div>
