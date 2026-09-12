<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\View\View;

class SalonController extends Controller
{
    public function show(Salon $salon): View
    {
        /*
        |--------------------------------------------------------------------------
        | Active salon
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $salon->is_active,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Salon rating
        |--------------------------------------------------------------------------
        */

        $salon->loadAvg(
            'reviews',
            'rating'
        );

        /*
        |--------------------------------------------------------------------------
        | Main relations
        |--------------------------------------------------------------------------
        */

        $salon->load([
            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */

            'owner',

            /*
            |--------------------------------------------------------------------------
            | Active barbers
            |--------------------------------------------------------------------------
            */

            'barbers' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('name');
            },

            /*
            |--------------------------------------------------------------------------
            | Active services
            |--------------------------------------------------------------------------
            */

            'services' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },

            /*
            |--------------------------------------------------------------------------
            | Working hours
            |--------------------------------------------------------------------------
            */

            'workingHours' => function ($query) {
                $query
                    ->orderBy('day_of_week')
                    ->orderBy('sort_order')
                    ->orderBy('start_time');
            },

            /*
            |--------------------------------------------------------------------------
            | Public posts
            |--------------------------------------------------------------------------
            |
            | Post can be:
            | photo
            | video
            | gif
            | reel
            |
            | Also load the barber / service associated
            | with each post.
            |
            */

            'posts' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->with([
                        'barber',
                        'service',
                    ])
                    ->orderBy('sort_order')
                    ->latest('id');
            },

            /*
            |--------------------------------------------------------------------------
            | Published salon reviews
            |--------------------------------------------------------------------------
            */

            'reviews' => function ($query) {
                $query
                    ->where('is_published', true)
                    ->with([
                        'customer',
                        'booking.service',
                    ])
                    ->latest();
            },
        ]);

        /*
        |--------------------------------------------------------------------------
        | Related salons
        |--------------------------------------------------------------------------
        |
        | Priority:
        | 1. Same district
        | 2. Same city
        | 3. Same province
        |
        | Current salon is always excluded.
        |
        */

        $relatedSalons = collect();

        /*
        |--------------------------------------------------------------------------
        | Same district
        |--------------------------------------------------------------------------
        */

        if (filled($salon->district)) {
            $relatedSalons = Salon::query()
                ->whereKeyNot($salon->id)
                ->where('is_active', true)
                ->where(
                    'district',
                    $salon->district
                )
                ->where(
                    'city',
                    $salon->city
                )
                ->withCount([
                    'services' => function ($query) {
                        $query->where(
                            'is_active',
                            true
                        );
                    },
                ])
                ->withAvg(
                    'reviews',
                    'rating'
                )
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(6)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Same city
        |--------------------------------------------------------------------------
        */

        if (
            $relatedSalons->count() < 6 &&
            filled($salon->city)
        ) {
            $existingIds = $relatedSalons
                ->pluck('id')
                ->push($salon->id);

            $citySalons = Salon::query()
                ->whereNotIn(
                    'id',
                    $existingIds
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'city',
                    $salon->city
                )
                ->withCount([
                    'services' => function ($query) {
                        $query->where(
                            'is_active',
                            true
                        );
                    },
                ])
                ->withAvg(
                    'reviews',
                    'rating'
                )
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(
                    6 -
                    $relatedSalons->count()
                )
                ->get();

            $relatedSalons = $relatedSalons
                ->concat($citySalons)
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Same province
        |--------------------------------------------------------------------------
        */

        if (
            $relatedSalons->count() < 6 &&
            filled($salon->province)
        ) {
            $existingIds = $relatedSalons
                ->pluck('id')
                ->push($salon->id);

            $provinceSalons = Salon::query()
                ->whereNotIn(
                    'id',
                    $existingIds
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'province',
                    $salon->province
                )
                ->withCount([
                    'services' => function ($query) {
                        $query->where(
                            'is_active',
                            true
                        );
                    },
                ])
                ->withAvg(
                    'reviews',
                    'rating'
                )
                ->orderByDesc('services_count')
                ->latest('id')
                ->limit(
                    6 -
                    $relatedSalons->count()
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
                fn ($value) =>
                filled($value)
            )
            ->map(
                fn ($value) =>
                trim((string) $value)
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
                'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode($coordinates);

            $mapsEmbedUrl =
                'https://www.google.com/maps?q='
                . rawurlencode($coordinates)
                . '&z=16&output=embed';
        } elseif ($fullAddress !== '') {
            $googleMapsUrl =
                'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode($fullAddress);

            $mapsEmbedUrl =
                'https://www.google.com/maps?q='
                . rawurlencode($fullAddress)
                . '&z=16&output=embed';
        }

        /*
        |--------------------------------------------------------------------------
        | Convenience data for public UI
        |--------------------------------------------------------------------------
        */

        $postsCount = $salon->posts->count();

        $reviewsCount =
            $salon->reviews->count();

        $barbersCount =
            $salon->barbers->count();

        $servicesCount =
            $salon->services->count();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

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
