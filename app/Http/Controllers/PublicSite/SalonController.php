<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalonController extends Controller
{
    public function show(Salon $salon): View
    {
        abort_unless(
            $salon->is_active,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Main relations
        |--------------------------------------------------------------------------
        */

        $salon->load([
            'owner',

            'barbers' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('name');
            },

            'services' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },

            'workingHours' => function ($query) {
                $query
                    ->orderBy('day_of_week')
                    ->orderBy('sort_order')
                    ->orderBy('start_time');
            },

            'posts' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->with([
                        'barber',
                        'service',
                    ])
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->limit(36);
            },

            'reviews' => function ($query) {
                $query
                    ->where('is_published', true)
                    ->with([
                        'customer',
                        'booking.service',
                    ])
                    ->latest()
                    ->limit(12);
            },
        ]);

        /*
        |--------------------------------------------------------------------------
        | Public counters
        |--------------------------------------------------------------------------
        |
        | Count from database, not from limited eager-loaded collections.
        |
        */

        $postsCount = $salon
            ->posts()
            ->where('is_active', true)
            ->count();

        $reviewsCount = $salon
            ->reviews()
            ->where('is_published', true)
            ->count();

        $barbersCount = $salon
            ->barbers()
            ->where('is_active', true)
            ->count();

        $servicesCount = $salon
            ->services()
            ->where('is_active', true)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Public rating
        |--------------------------------------------------------------------------
        |
        | Only published reviews affect public rating.
        |
        */

        $publicRating = $salon
            ->reviews()
            ->where('is_published', true)
            ->avg('rating');

        $salon->setAttribute(
            'reviews_avg_rating',
            $publicRating !== null
                ? (float) $publicRating
                : null
        );

        /*
        |--------------------------------------------------------------------------
        | Related salons
        |--------------------------------------------------------------------------
        */

        $relatedSalons = collect();

        if (filled($salon->district)) {
            $relatedSalons = Salon::query()
                ->where('id', '!=', $salon->id)
                ->where('is_active', true)
                ->where('district', $salon->district)
                ->where('city', $salon->city)
                ->withCount([
                    'services' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->withAvg(
                    [
                        'reviews' => function ($query) {
                            $query->where('is_published', true);
                        },
                    ],
                    'rating'
                )
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(6)
                ->get();
        }

        if (
            $relatedSalons->count() < 6 &&
            filled($salon->city)
        ) {
            $existingIds = $relatedSalons
                ->pluck('id')
                ->push($salon->id);

            $citySalons = Salon::query()
                ->whereNotIn('id', $existingIds)
                ->where('is_active', true)
                ->where('city', $salon->city)
                ->withCount([
                    'services' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->withAvg(
                    [
                        'reviews' => function ($query) {
                            $query->where('is_published', true);
                        },
                    ],
                    'rating'
                )
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(
                    6 - $relatedSalons->count()
                )
                ->get();

            $relatedSalons = $relatedSalons
                ->concat($citySalons)
                ->values();
        }

        if (
            $relatedSalons->count() < 6 &&
            filled($salon->province)
        ) {
            $existingIds = $relatedSalons
                ->pluck('id')
                ->push($salon->id);

            $provinceSalons = Salon::query()
                ->whereNotIn('id', $existingIds)
                ->where('is_active', true)
                ->where('province', $salon->province)
                ->withCount([
                    'services' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->withAvg(
                    [
                        'reviews' => function ($query) {
                            $query->where('is_published', true);
                        },
                    ],
                    'rating'
                )
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(
                    6 - $relatedSalons->count()
                )
                ->get();

            $relatedSalons = $relatedSalons
                ->concat($provinceSalons)
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Coordinates
        |--------------------------------------------------------------------------
        */

        $latitude = $salon->latitude;
        $longitude = $salon->longitude;

        $hasLocation =
            is_numeric($latitude) &&
            is_numeric($longitude);

        /*
        |--------------------------------------------------------------------------
        | Full address
        |--------------------------------------------------------------------------
        */

        $addressParts = collect([
            $salon->province,
            $salon->city,
            $salon->district,
            $salon->address,
        ])
            ->filter(
                fn ($value) => filled($value)
            )
            ->map(
                fn ($value) => trim((string) $value)
            )
            ->values();

        $fullAddress =
            $addressParts->implode('، ');

        /*
        |--------------------------------------------------------------------------
        | Google Maps
        |--------------------------------------------------------------------------
        */

        $googleMapsUrl = null;
        $mapsEmbedUrl = null;

        if ($hasLocation) {
            $coordinates =
                $latitude . ',' . $longitude;

            $googleMapsUrl =
                'https://www.google.com/maps/search/?api=1&query=' .
                rawurlencode($coordinates);

            $mapsEmbedUrl =
                'https://www.google.com/maps?q=' .
                rawurlencode($coordinates) .
                '&z=16&output=embed';
        } elseif ($fullAddress !== '') {
            $googleMapsUrl =
                'https://www.google.com/maps/search/?api=1&query=' .
                rawurlencode($fullAddress);

            $mapsEmbedUrl =
                'https://www.google.com/maps?q=' .
                rawurlencode($fullAddress) .
                '&z=16&output=embed';
        }

        return view(
            'public.salon',
            compact(
                'salon',
                'latitude',
                'longitude',
                'hasLocation',
                'fullAddress',
                'googleMapsUrl',
                'mapsEmbedUrl',
                'relatedSalons',
                'postsCount',
                'reviewsCount',
                'barbersCount',
                'servicesCount'
            )
        );
    }
}
