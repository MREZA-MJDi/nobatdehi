@props([
'service',
])

<a
    href="{{ route('salons.discover', ['q' => $service->name]) }}"
    class="discover-service-card reveal-item"
>
    <div
        class="discover-service-icon"
        aria-hidden="true"
    >
        <span>
            {{ mb_substr(trim($service->name), 0, 1) }}
        </span>
    </div>


    <div class="discover-service-info">

        <h3>
            {{ $service->name }}
        </h3>

        <span>
            پیدا کردن سالن
        </span>

    </div>


    <span
        class="discover-service-arrow"
        aria-hidden="true"
    >
        ↗
    </span>

</a>
