<div>
    {{-- Year filter --}}
    <div class="mb-8 flex flex-wrap items-center gap-3">
        <label for="year-filter" class="sr-only">{{ __('pencapaian.filter.label') }}</label>
        <select
            id="year-filter"
            wire:model.live="year"
            class="rounded-lg border border-border bg-white px-4 py-2 text-body-sm text-text
                   focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                   transition-colors duration-short"
        >
            <option value="">{{ __('pencapaian.filter.all_years') }}</option>
            @foreach($years as $y)
                <option value="{{ (int) $y }}">{{ (int) $y }}</option>
            @endforeach
        </select>
    </div>

    {{-- Results --}}
    @if($achievements->isEmpty())
        <div class="rounded-lg border border-border-light bg-bg-washed px-6 py-12 text-center">
            <p class="text-body-md text-muted">{{ __('pencapaian.no_results') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($achievements as $achievement)
                <x-pencapaian.achievement-card :achievement="$achievement" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $achievements->links() }}
        </div>
    @endif
</div>
