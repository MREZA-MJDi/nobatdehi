@props([
'text',
'as' => 'span',
'delay' => 0,
'stagger' => 0.045,
'once' => true,
])

@php
    $allowedTags = ['h1', 'h2', 'h3', 'p', 'span'];
    $tag = in_array($as, $allowedTags, true) ? $as : 'span';

    $words = preg_split('/\s+/u', trim($text));
@endphp

<{{ $tag }}
    data-text-reveal
    data-text-reveal-delay="{{ $delay }}"
    data-text-reveal-stagger="{{ $stagger }}"
    data-text-reveal-once="{{ $once ? 'true' : 'false' }}"
    {{ $attributes->merge([
        'class' => 'inline',
    ]) }}
>
    {{-- Accessible text --}}
    <span class="sr-only">
        {{ $text }}
    </span>

    {{-- Animated text --}}
    <span aria-hidden="true" class="inline">
        @foreach ($words as $index => $word)
            <span
                data-text-reveal-word
                class="inline-block will-change-transform"
                style="
                    opacity: 0;
                    transform: translateY(12px);
                    filter: blur(8px);
                "
            >{{ $word }}</span>@if ($index < count($words) - 1){{ "\u{00A0}" }}@endif
        @endforeach
    </span>
</{{ $tag }}>
