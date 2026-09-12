@php
    /** @var \App\Models\Salon $salon */
    $variant  = $variant ?? 'default';
    $cover    = $salon->cover_path ? asset('storage/'.$salon->cover_path) : asset('img/salon-placeholder.jpg');
    $rating   = (float) $salon->reviews_avg_rating;
    $reviews  = (int) $salon->reviews_count;
    $minPrice = $salon->services->min('price');
    $distance = $salon->distance_km ?? null;
@endphp

<article class="dc-card dc-card--{{ $variant }}" data-salon-id="{{ $salon->id }}">
    <div class="dc-card__media">
        <img src="{{ $cover }}" alt="{{ $salon->name }}" loading="lazy">
        <div class="dc-card__badges">
            @if($rating >= 4.5)
                <span class="dc-badge dc-badge--top">محبوب</span>
            @endif
        </div>
    </div>

    <div class="dc-card__body">
        <h3 class="dc-card__name">
            <a href="{{ route('public.salons.show', $salon) }}">{{ $salon->name }}</a>
        </h3>

        <div class="dc-card__meta">
            <span class="dc-meta-item dc-meta-item--rating">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.3L22 9.3l-5 4.8 1.2 6.9L12 17.8 5.8 21l1.2-6.9-5-4.8 7.1-1z"/></svg>
                @if($reviews > 0)
                    <b>{{ number_format($rating, 1) }}</b>
                    <span>({{ number_format($reviews) }})</span>
                @else
                    <span class="dc-meta-item--muted">جدید</span>
                @endif
            </span>

            @if($salon->district || $salon->city)
                <span class="dc-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-5.5-7-11a7 7 0 1114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    {{ $salon->district ?: $salon->city }}
                </span>
            @endif

            @if($distance)
                <span class="dc-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    {{ number_format($distance, 1) }} کیلومتر
                </span>
            @endif
        </div>

        @if($salon->services->isNotEmpty())
            <div class="dc-card__services">
                @foreach($salon->services->take(2) as $srv)
                    <span class="dc-service-tag">{{ $srv->name }}</span>
                @endforeach
            </div>
        @endif

        <div class="dc-card__foot">
            <div class="dc-card__price">
                @if($minPrice)
                    <small>شروع از</small>
                    <b>{{ number_format($minPrice) }}<span>تومان</span></b>
                @endif
            </div>

            <a href="{{ route('public.salons.show', $salon) }}" class="dc-card__cta">
                رزرو
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M15 6l-6 6 6 6"/></svg>
            </a>
        </div>
    </div>
</article>
