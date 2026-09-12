@extends('layouts.discovery')

@section('title', 'NOBAT — کشف و رزرو نوبت')

@section(
    'description',
    'بهترین سالن‌ها، متخصص‌ها و خدمات زیبایی را پیدا کن و آنلاین نوبت بگیر.'
)

@php
    use Illuminate\Support\Facades\Storage;

    /*
    |--------------------------------------------------------------------------
    | IMAGE RESOLVER
    |--------------------------------------------------------------------------
    */

    $resolveImage = function ($path) {
        if (!$path) {
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

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $salons = ($nearbySalons ?? collect())->values();

    $services = ($popularServices ?? collect())->values();

    $heroSalon =
        $featuredSalon
        ?? $salons->first();

    /*
    |--------------------------------------------------------------------------
    | HERO IMAGE
    |--------------------------------------------------------------------------
    */

    $fallbackHero =
        'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1800&q=88';

    $heroImage = null;

    if ($heroSalon) {
        $heroImage =
            $resolveImage($heroSalon->cover_path)
            ?: $resolveImage($heroSalon->logo_path);
    }

    $heroImage =
        $heroImage ?: $fallbackHero;

    /*
    |--------------------------------------------------------------------------
    | SERVICE FALLBACK IMAGES
    |--------------------------------------------------------------------------
    */

    $serviceFallbacks = [

        'مو' =>
            'https://images.unsplash.com/photo-1562322140-8baeececf3df?auto=format&fit=crop&w=1000&q=85',

        'رنگ' =>
            'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1000&q=85',

        'ناخن' =>
            'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=1000&q=85',

        'پوست' =>
            'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=1000&q=85',

        'ماساژ' =>
            'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1000&q=85',

        'میکاپ' =>
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=1000&q=85',
    ];

    $defaultServiceImage = $fallbackHero;

    /*
    |--------------------------------------------------------------------------
    | STATS FALLBACK
    |--------------------------------------------------------------------------
    */

    $stats = $stats ?? [
        'salons' => $salons->count(),

        'barbers' =>
            $salons
                ->flatMap
                ->barbers
                ->count(),

        'services' => $services->count(),

        'bookings' => 0,
    ];
@endphp


@section('content')

    <div
        id="discoveryPage"
        class="discovery-page"
    >

        @include('customer.discover.partials.header')

        <main>

            @include('customer.discover.sections.hero')

            @include('customer.discover.sections.popular-salons')

            @include('customer.discover.sections.services')

            @include('customer.discover.sections.featured')

            @include('customer.discover.sections.stylists')

            @include('customer.discover.sections.nearby')

            @include('customer.discover.sections.booking')

            @include('customer.discover.sections.stats')

            @include('customer.discover.sections.blog')

            @include('customer.discover.sections.owner-cta')

        </main>

    </div>

@endsection
