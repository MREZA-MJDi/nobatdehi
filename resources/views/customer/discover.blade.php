@extends('layouts.public')

@section('discover_page', '1')

@section('title', 'کشف سالن و رزرو نوبت')

@section(
    'description',
    'سالن‌ها، آرایشگرها و خدمات زیبایی را پیدا کن، مقایسه کن و آنلاین نوبت بگیر.'
)

@section('canonical', route('salons.discover'))

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

        @include('customer.discover.sections.results')
        @include('customer.discover.sections.services')
        @include('customer.discover.sections.popular-salons')
        @include('customer.discover.sections.featured')
        @include('customer.discover.sections.stylists')

        @if($hasGeo && $nearbySalons->isNotEmpty())
            @include('customer.discover.sections.nearby')
        @endif

        @include('customer.discover.sections.stats')
        @include('customer.discover.sections.owner-cta')
    </div>
@endsection
