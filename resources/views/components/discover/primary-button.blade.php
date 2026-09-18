@props([
'href' => '#',
'text' => 'مشاهده',
'type' => 'link',
])

@if($type === 'button')

    <button
        type="button"
        {{ $attributes->merge([
            'class' => 'discover-primary-button'
        ]) }}
    >
        <span>
            {{ $text }}
        </span>

        <span aria-hidden="true">
            ←
        </span>
    </button>

@else

    <a
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => 'discover-primary-button'
        ]) }}
    >
        <span>
            {{ $text }}
        </span>

        <span aria-hidden="true">
            ←
        </span>
    </a>

@endif
