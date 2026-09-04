<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(
        Request $request,
        AvailabilityService $availabilityService
    ): View {
        $query = trim((string) $request->query('q', ''));
        $code = strtoupper(trim((string) $request->query('code', '')));
        $type = (string) $request->query('type', 'all');
        $sort = (string) $request->query('sort', 'recommended');
        $city = trim((string) $request->query('city', ''));

        $latitude = $this->nullableFloat($request->query('lat'));
        $longitude = $this->nullableFloat($request->query('lng'));
        $hasLocation = $latitude !== null && $longitude !== null;

        if (! in_array($type, ['all', 'salon', 'barber'], true)) {
            $type = 'all';
        }

        if (! in_array($sort, ['recommended', 'rating', 'newest', 'nearest'], true)) {
            $sort = 'recommended';
        }

        if ($sort === 'nearest' && ! $hasLocation) {
            $sort = 'recommended';
        }

        /* -------------------------------------------------------------
         | Salon results
         * ------------------------------------------------------------- */
        $salonQuery = Salon::query()
            ->where('is_active', true)
            ->withCount([
                'barbers' => fn ($q) => $q->where('is_active', true),
                'services' => fn ($q) => $q->where('is_active', true),
                'reviews' => fn ($q) => $q->where('is_published', true),
            ])
            ->withAvg([
                'reviews' => fn ($q) => $q->where('is_published', true),
            ], 'rating');

        if ($type === 'barber') {
            $salonQuery->whereRaw('1 = 0');
        } else {
            $this->applySalonSearch($salonQuery, $query, $code, $city);
        }

        if ($hasLocation) {
            $salonQuery->select('salons.*')->selectRaw(
                $this->distanceSql() . ' AS distance_km',
                [$latitude, $latitude, $longitude]
            );
        }

        match ($sort) {
            'nearest' => $salonQuery
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance_km')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count'),

            'rating' => $salonQuery
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count')
                ->latest('id'),

            'newest' => $salonQuery->latest('id'),

            default => $salonQuery
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count')
                ->latest('id'),
        };

        $salons = $salonQuery
            ->paginate(12)
            ->withQueryString();

        /* -------------------------------------------------------------
         | Popular specialists
         | Rating belongs to Salon; specialist popularity is completed bookings.
         * ------------------------------------------------------------- */
        $barberQuery = Barber::query()
            ->where('is_active', true)
            ->whereHas('salon', function ($q) use ($city) {
                $q->where('is_active', true);

                if ($city !== '') {
                    $q->where('city', $city);
                }
            })
            ->with([
                'salon' => function ($q) {
                    $q->withCount([
                        'reviews' => fn ($r) => $r->where('is_published', true),
                    ])->withAvg([
                        'reviews' => fn ($r) => $r->where('is_published', true),
                    ], 'rating');
                },
            ])
            ->withCount([
                'bookings as completed_bookings_count' => fn ($q) =>
                $q->where('status', BookingStatus::COMPLETED->value),
            ]);

        if ($query !== '') {
            $like = '%' . $query . '%';
            $barberQuery->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('specialty', 'like', $like)
                    ->orWhere('bio', 'like', $like);
            });
        }

        $barbers = $barberQuery
            ->orderByDesc('completed_bookings_count')
            ->latest('id')
            ->limit(12)
            ->get();

        if ($hasLocation) {
            $barbers = $barbers->map(function ($barber) use ($latitude, $longitude) {
                $salon = $barber->salon;
                $barber->distance_km = $salon?->latitude !== null && $salon?->longitude !== null
                    ? $this->distanceInKm(
                        $latitude,
                        $longitude,
                        (float) $salon->latitude,
                        (float) $salon->longitude,
                    )
                    : null;

                return $barber;
            });

            if ($sort === 'nearest') {
                $barbers = $barbers
                    ->sortBy(fn ($barber) => $barber->distance_km ?? PHP_FLOAT_MAX)
                    ->values();
            }
        }

        /* -------------------------------------------------------------
         | Cities
         * ------------------------------------------------------------- */
        $cities = Salon::query()
            ->where('is_active', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        /* -------------------------------------------------------------
         | Marketplace categories
         | No Category model exists yet, so these remain entry points.
         * ------------------------------------------------------------- */
        $categories = [
            ['title' => 'مو', 'subtitle' => 'کوتاهی، رنگ و کراتین', 'query' => 'مو', 'icon' => '✂'],
            ['title' => 'ناخن', 'subtitle' => 'مانیکور، ژل و پدیکور', 'query' => 'ناخن', 'icon' => '✦'],
            ['title' => 'میکاپ', 'subtitle' => 'میکاپ و شینیون', 'query' => 'میکاپ', 'icon' => '✧'],
            ['title' => 'پوست', 'subtitle' => 'فیشال و مراقبت پوست', 'query' => 'پوست', 'icon' => '◌'],
            ['title' => 'ابرو', 'subtitle' => 'اصلاح و فرم‌دهی', 'query' => 'ابرو', 'icon' => '⌁'],
        ];

        /* -------------------------------------------------------------
         | Featured salons
         * ------------------------------------------------------------- */
        $featuredSalons = Salon::query()
            ->where('is_active', true)
            ->withCount([
                'reviews' => fn ($q) => $q->where('is_published', true),
            ])
            ->withAvg([
                'reviews' => fn ($q) => $q->where('is_published', true),
            ], 'rating')
            ->with([
                'portfolioItems' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->latest('id'),
            ])
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->latest('id')
            ->limit(8)
            ->get();

        /* -------------------------------------------------------------
         | Real availability preview
         * ------------------------------------------------------------- */
        $availableSlots = collect();

        if ($type !== 'barber') {
            $availabilityCandidates = Salon::query()
                ->where('is_active', true)
                ->when($city !== '', fn ($q) => $q->where('city', $city))
                ->when($query !== '', function ($q) use ($query) {
                    $like = '%' . $query . '%';
                    $q->where(function ($nested) use ($like) {
                        $nested->where('name', 'like', $like)
                            ->orWhere('city', 'like', $like)
                            ->orWhere('district', 'like', $like)
                            ->orWhereHas('services', fn ($service) =>
                            $service->where('is_active', true)->where('name', 'like', $like)
                            );
                    });
                })
                ->with([
                    'barbers' => fn ($q) => $q->where('is_active', true)->latest('id'),
                    'services' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
                ])
                ->when($hasLocation, function ($q) use ($latitude, $longitude) {
                    $q->select('salons.*')->selectRaw(
                        $this->distanceSql() . ' AS distance_km',
                        [$latitude, $latitude, $longitude]
                    )->orderBy('distance_km');
                }, fn ($q) => $q->latest('id'))
                ->limit(8)
                ->get();

            foreach ($availabilityCandidates as $salon) {
                $found = $this->firstAvailableForSalon(
                    $salon,
                    $availabilityService,
                    Carbon::today(),
                    Carbon::tomorrow(),
                );

                if ($found) {
                    $availableSlots->push($found);
                }
            }
        }

        /* -------------------------------------------------------------
         | Map data
         * ------------------------------------------------------------- */
        $mapQuery = Salon::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        $this->applySalonSearch($mapQuery, $query, $code, $city);

        if ($hasLocation) {
            $mapQuery->select([
                'salons.id',
                'salons.name',
                'salons.slug',
                'salons.latitude',
                'salons.longitude',
                'salons.city',
                'salons.district',
            ])->selectRaw(
                $this->distanceSql() . ' AS distance_km',
                [$latitude, $latitude, $longitude]
            );
        } else {
            $mapQuery->select([
                'id', 'name', 'slug', 'latitude', 'longitude', 'city', 'district',
            ]);
        }

        if ($sort === 'nearest' && $hasLocation) {
            $mapQuery->orderBy('distance_km');
        } else {
            $mapQuery->latest('id');
        }

        $mapSalons = $mapQuery->limit(50)->get();

        /* -------------------------------------------------------------
         | Hero counts
         * ------------------------------------------------------------- */
        $activeSalonCount = Salon::query()->where('is_active', true)->count();
        $activeBarberCount = Barber::query()
            ->where('is_active', true)
            ->whereHas('salon', fn ($q) => $q->where('is_active', true))
            ->count();
        $publishedReviewCount = \App\Models\Review::query()
            ->where('is_published', true)
            ->count();

        $hasSearch = $query !== '' || $code !== '' || $city !== '';

        $seoTitle = match (true) {
            $type === 'barber' => 'کشف آرایشگرها | RM نوبت‌دهی',
            $sort === 'nearest' => 'نزدیک‌ترین سالن‌ها | RM نوبت‌دهی',
            $sort === 'rating' => 'محبوب‌ترین سالن‌ها | RM نوبت‌دهی',
            $hasSearch => 'جستجوی سالن و آرایشگر | RM نوبت‌دهی',
            default => 'کشف سالن و آرایشگر | RM نوبت‌دهی',
        };

        return view('customer.discover', [
            'salons' => $salons,
            'barbers' => $barbers,
            'mapSalons' => $mapSalons,
            'cities' => $cities,
            'categories' => $categories,
            'featuredSalons' => $featuredSalons,
            'availableSlots' => $availableSlots,
            'activeSalonCount' => $activeSalonCount,
            'activeBarberCount' => $activeBarberCount,
            'publishedReviewCount' => $publishedReviewCount,
            'query' => $query,
            'code' => $code,
            'type' => $type,
            'sort' => $sort,
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hasLocation' => $hasLocation,
            'seoTitle' => $seoTitle,
            'seoDescription' => 'سالن، آرایشگر یا خدمت موردنظرت را پیدا کن، نظر مشتری‌ها را ببین و آنلاین نوبت بگیر.',
            'robots' => $hasSearch ? 'noindex,follow' : 'index,follow',
        ]);
    }

    private function applySalonSearch($queryBuilder, string $query, string $code, string $city): void
    {
        if ($query !== '') {
            $like = '%' . $query . '%';
            $queryBuilder->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhereHas('services', fn ($service) =>
                    $service->where('is_active', true)->where('name', 'like', $like)
                    );
            });
        }

        if ($code !== '') {
            $queryBuilder->where('code', $code);
        }

        if ($city !== '') {
            $queryBuilder->where('city', $city);
        }
    }

    private function firstAvailableForSalon(
        Salon $salon,
        AvailabilityService $availabilityService,
        Carbon $today,
        Carbon $tomorrow,
    ): ?array {
        foreach ($salon->barbers as $barber) {
            foreach ($salon->services as $service) {
                foreach ([$today, $tomorrow] as $date) {
                    $slots = $availabilityService->slots($salon, $barber, $service, $date);
                    $slot = collect($slots)->firstWhere('available', true);

                    if ($slot) {
                        return [
                            'salon' => $salon,
                            'barber' => $barber,
                            'service' => $service,
                            'slot' => $slot,
                            'date' => $date->copy(),
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function distanceSql(): string
    {
        return '6371 * 2 * ASIN(SQRT(POWER(SIN(RADIANS(salons.latitude - ?) / 2), 2) + COS(RADIANS(?)) * COS(RADIANS(salons.latitude)) * POWER(SIN(RADIANS(salons.longitude - ?) / 2), 2)))';
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $value = (float) $value;

        return is_finite($value) ? $value : null;
    }

    private function distanceInKm(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2,
    ): float {
        $earthRadius = 6371;
        $latDelta = deg2rad($latitude2 - $latitude1);
        $lngDelta = deg2rad($longitude2 - $longitude1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($latitude1))
            * cos(deg2rad($latitude2))
            * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
