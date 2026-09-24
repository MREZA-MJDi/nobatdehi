@extends('layouts.customer')

@section('title', 'کشف سالن و رزرو نوبت')

@section(
    'meta_description',
    'سالن‌ها، آرایشگرها و خدمات زیبایی را پیدا کن، مقایسه کن و آنلاین نوبت بگیر.'
)

@section('canonical', route('salons.discover'))

@push('head')
    @vite('resources/css/discovery.css')
@endpush

@push('scripts')
    @vite('resources/js/discover.js')
@endpush

@php
    use Illuminate\Support\Facades\Storage;

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

@endphp

@section('content')

    <div class="discover-page">

        @include('customer.discover.sections.hero')

        <div id="discoverDynamicContent">
            {{-- =====================================================
                MAIN SEARCH RESULTS
            ====================================================== --}}
            @include('customer.discover.sections.results')
        </div>

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

                <div class="discover-location-kicker">نزدیک من</div>

                <h2 id="discoverLocationTitle">سالن‌های نزدیکت را پیدا کنیم؟</h2>

                <p class="discover-location-copy">
                    اول موقعیتت را از مرورگر می‌گیریم؛ بعد سالن‌های فعال را در
                    <strong>شعاع ۱۵ کیلومتر</strong> بررسی می‌کنیم، فاصله واقعی را حساب
                    می‌کنیم و نزدیک‌ترین گزینه‌ها را اول نشان می‌دهیم.
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
                        اجازه موقعیت و پیدا کردن سالن‌های اطراف
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
