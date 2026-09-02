@props([
'href' => null,
'label' => 'بازگشت',
])

<div class="mb-4">

    @if($href)

        <a
            href="{{ $href }}"
            {{ $attributes->merge([
                'class' => 'inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600'
            ]) }}
        >
            <span aria-hidden="true">
                →
            </span>

            {{ $label }}
        </a>

    @else

        <button
            type="button"
            onclick="window.history.back()"
            {{ $attributes->merge([
                'class' => 'inline-flex items-center gap-2 text-xs font-bold text-content-muted transition hover:text-accent-600'
            ]) }}
        >
            <span aria-hidden="true">
                →
            </span>

            {{ $label }}
        </button>

    @endif

</div>
