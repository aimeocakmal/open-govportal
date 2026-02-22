<x-layouts.app>
    <div class="bg-warning-50 border-b border-warning-200 px-4 py-3 text-center text-body-sm text-warning-800">
        {{ __('common.preview_banner') }}
    </div>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6">
            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-body-xs font-medium text-text-secondary">
                {{ ucfirst($modelType) }}
            </span>
            @if(isset($record->status))
                <span class="ml-2 inline-flex items-center rounded-full px-3 py-1 text-body-xs font-medium {{ $record->status === 'published' ? 'bg-success-50 text-success-700' : 'bg-warning-50 text-warning-700' }}">
                    {{ ucfirst($record->status) }}
                </span>
            @endif
        </div>

        <h1 class="mb-4 font-heading text-heading-md font-semibold text-text">
            {{ $record->title_ms ?? $record->name ?? __('common.preview_title') }}
        </h1>

        @if($record->title_en ?? null)
            <h2 class="mb-6 font-heading text-heading-2xs text-muted">{{ $record->title_en }}</h2>
        @endif

        @if($record->content_ms ?? $record->description_ms ?? null)
            <div class="prose mb-8 max-w-none">
                <h3 class="font-heading text-body-lg font-semibold text-gray-700">Bahasa Malaysia</h3>
                <div>{!! $record->content_ms ?? $record->description_ms !!}</div>
            </div>
        @endif

        @if($record->content_en ?? $record->description_en ?? null)
            <div class="prose mb-8 max-w-none">
                <h3 class="font-heading text-body-lg font-semibold text-gray-700">English</h3>
                <div>{!! $record->content_en ?? $record->description_en !!}</div>
            </div>
        @endif

        <div class="mt-8 border-t border-border-light pt-6">
            <dl class="grid grid-cols-2 gap-4 text-body-sm text-muted">
                @if($record->created_at ?? null)
                    <div>
                        <dt class="font-medium text-text-secondary">{{ __('common.created_at') }}</dt>
                        <dd>{{ $record->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                @endif
                @if($record->updated_at ?? null)
                    <div>
                        <dt class="font-medium text-text-secondary">{{ __('common.updated_at') }}</dt>
                        <dd>{{ $record->updated_at->format('d M Y, H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</x-layouts.app>
