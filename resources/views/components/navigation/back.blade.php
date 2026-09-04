@props([
'href' => null,
'label' => 'بازگشت',
])

<div class="mb-5">
    @php
        $classes = '
            inline-flex
            items-center
            gap-2
            text-sm
            font-semibold
            text-content-muted
            transition-colors
            duration-200
            hover:text-content
            focus-visible:outline-none
            focus-visible:ring-2
            focus-visible:ring-accent-500/30
            rounded-lg
            px-1
            py-1
        ';
    @endphp

    @if($href)

        <a
            href="{{ $href }}"
            {{ $attributes->merge(['class' => $classes]) }}
        >
            <svg
                width="17"
                height="17"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M5 12h14" />
                <path d="m13 18 6-6-6-6" />
            </svg>
            <span>{{ $label }}</span>
        </a>

    @else

        <button
            type="button"
            onclick="window.history.back()"
            {{ $attributes->merge(['class' => $classes]) }}
        >
            <svg
                width="17"
                height="17"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M19 12H5" />
                <path d="m11 18-6-6 6-6" />
            </svg>

            <span>{{ $label }}</span>
        </button>

    @endif
</div>
