<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{ state: $wire.$entangle(@js($getStatePath())) }"
        style="display: flex; flex-wrap: wrap; gap: 0.75rem;"
    >
        @foreach ($getColorOptions() as $name => $hex)
            <button
                type="button"
                x-on:click="state = @js($name)"
                :style="'display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; border: none; cursor: pointer; position: relative; background-color: {{ $hex }};' + (state === @js($name) ? ' box-shadow: 0 0 0 2px white, 0 0 0 4px {{ $hex }}; transform: scale(1.1);' : ' opacity: 0.8;')"
                title="{{ ucfirst($name) }}"
            >
                <svg
                    x-show="state === @js($name)"
                    x-transition
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="white"
                    style="width: 1.25rem; height: 1.25rem; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.3));"
                >
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                </svg>
            </button>
        @endforeach
    </div>
</x-dynamic-component>
