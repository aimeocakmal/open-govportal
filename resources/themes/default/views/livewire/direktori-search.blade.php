<div>
    {{-- Search + Filter bar --}}
    <div class="mb-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
        {{-- Search input --}}
        <div class="relative flex-1">
            <label for="staff-search" class="sr-only">{{ __('direktori.search.label') }}</label>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="size-5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                id="staff-search"
                type="search"
                wire:model.live.debounce.400ms="query"
                placeholder="{{ __('direktori.search.placeholder') }}"
                class="w-full rounded-lg border border-border bg-white py-2.5 pl-10 pr-4 text-body-sm text-text
                       placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                       transition-colors duration-short"
            >
        </div>

        {{-- Department filter --}}
        <div class="sm:w-64">
            <label for="department-filter" class="sr-only">{{ __('direktori.filter.label') }}</label>
            <select
                id="department-filter"
                wire:model.live="jabatan"
                class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-body-sm text-text
                       focus:border-primary focus:ring-2 focus:ring-primary-200 focus:outline-none
                       transition-colors duration-short"
            >
                <option value="">{{ __('direktori.filter.all_departments') }}</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Results --}}
    @if($staff->isEmpty())
        <div class="rounded-lg border border-border-light bg-bg-washed px-6 py-12 text-center">
            <p class="text-body-md text-muted">{{ __('direktori.no_results') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($staff as $member)
                <x-direktori.staff-card :staff="$member" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $staff->links() }}
        </div>
    @endif
</div>
