@extends('layouts.customer')

@section('title', 'کشف سالن و رزرو نوبت')

@section(
    'meta_description',
    'سالن‌ها، آرایشگرها و خدمات زیبایی را پیدا کن، مقایسه کن و آنلاین نوبت بگیر.'
)

@section('canonical', route('salons.discover'))

@push('head')
    @vite('resources/css/discovery.css')
    @vite('resources/js/discover.js')
@endpush

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $resolveImage = function ($path) {
        if (! $path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '/')
        ) {
            return $path;
        }

        return Storage::url($path);
    };

    $heroSalon = $featuredSalon ?? $popularSalons->first();

    $heroImage = null;

    if ($heroSalon) {
        $heroImage =
            ($heroSalon->cover_url ?? null)
            ?: $resolveImage($heroSalon->cover_path ?? null)
            ?: $resolveImage($heroSalon->logo_path ?? null);
    }

    $heroImage ??= 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1800&q=88';

    $serviceFallbacks = [
        'مو'    => 'https://images.unsplash.com/photo-1562322140-8baeececf3df?auto=format&fit=crop&w=1000&q=85',
        'رنگ'   => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1000&q=85',
        'ناخن'  => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=1000&q=85',
        'پوست'  => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=1000&q=85',
        'ماساژ' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1000&q=85',
        'میکاپ' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=1000&q=85',
    ];
@endphp

@section('content')

    <div class="discover-page">

        @include('customer.discover.sections.hero')

        @if($hasGeo && $nearbySalons->isNotEmpty())
            @include('customer.discover.sections.nearby')
        @endif

        {{-- =========================================================
            MAIN SEARCH RESULTS
        ========================================================== --}}
        @include('customer.discover.sections.results')

        {{-- =========================================================
            SECONDARY DISCOVERY CONTENT
        ========================================================== --}}

        @include('customer.discover.sections.services')

        @include('customer.discover.sections.popular-salons')

        @include('customer.discover.sections.stylists')

        @include('customer.discover.sections.stats')

        @include('customer.discover.sections.owner-cta')

        <div
            class="discover-location-modal"
            id="discoverLocationModal"
            aria-hidden="true"
            hidden
        >
            <div class="discover-location-backdrop" data-discover-location-close></div>

            <div
                class="discover-location-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="discoverLocationTitle"
            >
                <button
                    type="button"
                    class="discover-location-close"
                    data-discover-location-close
                    aria-label="بستن"
                >×</button>

                <div class="discover-location-kicker">NEARBY SEARCH</div>

                <h2 id="discoverLocationTitle">سالن‌های نزدیکت را پیدا کنیم؟</h2>

                <p class="discover-location-copy">
                    موقعیت مکانی‌ات فقط برای پیدا کردن سالن‌های اطراف استفاده می‌شود.
                    بعد از پیدا شدن موقعیت، نتیجه‌ها بر اساس فاصله مرتب می‌شوند.
                </p>

                <div class="discover-location-map" id="discoverLocationMap">
                    <div class="discover-location-map-state" id="discoverLocationState">
                        <span class="discover-location-map-pin">⌖</span>
                        <strong>موقعیت خودت را مشخص کن</strong>
                        <small>برای نمایش گزینه‌های اطراف، اجازه موقعیت مکانی مرورگر را بده.</small>
                    </div>

                    <iframe
                        id="discoverLocationMapFrame"
                        title="موقعیت مکانی"
                        loading="lazy"
                        hidden
                    ></iframe>
                </div>

                <div class="discover-location-actions">
                    <button
                        type="button"
                        class="discover-location-primary"
                        id="discoverLocationAllow"
                    >
                        اجازه موقعیت و پیدا کردن نزدیک‌ترین‌ها
                    </button>

                    <button
                        type="button"
                        class="discover-location-secondary"
                        data-discover-location-close
                    >
                        فعلاً نه
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
