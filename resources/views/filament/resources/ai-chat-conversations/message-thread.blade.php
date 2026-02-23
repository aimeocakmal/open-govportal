<div class="space-y-4">
    @forelse ($getRecord()->messages()->orderBy('created_at')->get() as $message)
        <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[80%] rounded-lg px-4 py-3 {{ $message->role === 'user' ? 'bg-primary-100 dark:bg-primary-900/30' : 'bg-gray-100 dark:bg-gray-800' }}">
                <div class="mb-1 flex items-center gap-2">
                    <span class="text-xs font-semibold {{ $message->role === 'user' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $message->role === 'user' ? __('ai.role_user') : __('ai.role_assistant') }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $message->created_at?->format('H:i:s') }}
                    </span>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! nl2br(e($message->content)) !!}
                </div>
                @if ($message->role === 'assistant' && ($message->prompt_tokens || $message->completion_tokens))
                    <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                        {{ $message->prompt_tokens ?? 0 }} {{ __('ai.prompt_tokens') }} / {{ $message->completion_tokens ?? 0 }} {{ __('ai.completion_tokens') }}
                        @if ($message->duration_ms)
                            &middot; {{ $message->duration_ms }}ms
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('ai.no_messages') }}</p>
    @endforelse
</div>
