@props([
'value' => 0,
'startValue' => 0,
'delay' => 0,
'decimalPlaces' => 0,
'prefix' => '',
'suffix' => '',
])

@php
    $initialValue = number_format(
        $startValue,
        $decimalPlaces,
        '.',
        ','
    );
@endphp

<span
    data-number-ticker
    data-number-ticker-value="{{ $value }}"
    data-number-ticker-start="{{ $startValue }}"
    data-number-ticker-delay="{{ $delay }}"
    data-number-ticker-decimals="{{ $decimalPlaces }}"
    data-number-ticker-prefix="{{ $prefix }}"
    data-number-ticker-suffix="{{ $suffix }}"
    data-number-ticker-initial="{{ $initialValue }}"
    {{ $attributes->merge([
        'class' => 'inline-block tabular-nums',
    ]) }}
>
    {{ $prefix }}{{ $initialValue }}{{ $suffix }}
</span>
