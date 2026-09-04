@props([
'people' => [],
'extra' => null,
])

@php
    $tones = [
        'bg-blue-600',
        'bg-slate-600',
        'bg-sky-700',
        'bg-indigo-600',
        'bg-slate-700',
        'bg-blue-800',
    ];

    $toPersianNumber = function ($value) {
        return strtr((string) $value, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ]);
    };
@endphp

<div
    data-slot="avatar-circles"
    {{ $attributes->merge([
        'class' => 'flex -space-x-3 rtl:space-x-reverse',
    ]) }}
>
    @foreach ($people as $index => $name)
        @php
            $parts = preg_split('/\s+/u', trim($name));
            $initials = collect($parts)
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_substr($part, 0, 1))
                ->implode('');
        @endphp

        <span
            title="{{ $name }}"
            class="{{ $tones[$index % count($tones)] }} flex size-10 items-center justify-center rounded-full text-xs font-semibold text-white ring-2 ring-background"
        >
            {{ $initials }}
        </span>
    @endforeach

    @if ($extra)
        @php
            $extraLabel = $extra >= 1000
                ? $toPersianNumber(floor($extra / 1000)) . 'هزار'
                : $toPersianNumber($extra);
        @endphp

        <span
            class="flex size-10 items-center justify-center rounded-full bg-muted text-xs font-semibold ring-2 ring-background"
        >
            +{{ $extraLabel }}
        </span>
    @endif
</div>
