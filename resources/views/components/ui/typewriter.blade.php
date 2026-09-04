@props([
'words' => [],
'typeSpeed' => 70,
'deleteSpeed' => 40,
'holdTime' => 1800,
'loop' => true,
'cursor' => true,
])

@php
    $wordsJson = htmlspecialchars(
        json_encode($words, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ENT_QUOTES,
        'UTF-8'
    );

    $longest = collect($words)->sortByDesc(fn ($word) => mb_strlen($word))->first() ?? '';
@endphp

<span
    data-typewriter
    data-typewriter-words="{{ $wordsJson }}"
    data-typewriter-type-speed="{{ $typeSpeed }}"
    data-typewriter-delete-speed="{{ $deleteSpeed }}"
    data-typewriter-hold-time="{{ $holdTime }}"
    data-typewriter-loop="{{ $loop ? 'true' : 'false' }}"
    data-typewriter-cursor="{{ $cursor ? 'true' : 'false' }}"
    dir="rtl"
    {{ $attributes->merge([
        'class' => 'inline-grid align-middle',
    ]) }}
>
    {{-- Accessibility --}}
    <span class="sr-only">
        {{ $words[0] ?? '' }}
    </span>

    {{-- Invisible longest word reserves the final width --}}
    <span
        aria-hidden="true"
        class="invisible col-start-1 row-start-1 whitespace-nowrap"
    >
        {{ $longest }}

        @if ($cursor)
            <span class="font-light" dir="ltr">|</span>
        @endif
    </span>

    {{-- Animated text --}}
    <span
        aria-hidden="true"
        data-typewriter-text
        class="col-start-1 row-start-1 whitespace-nowrap"
    >
        @if ($words[0] ?? false)
            {{ $words[0] }}
        @endif

        @if ($cursor)
            <span
                data-typewriter-cursor
                class="animate-pulse font-light"
                dir="ltr"
            >|</span>
        @endif
    </span>
</span>
