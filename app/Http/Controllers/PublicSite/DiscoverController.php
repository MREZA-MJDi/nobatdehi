<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Review;
use App\Models\Salon;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

        $hasLocation =
            $latitude !== null &&
            $longitude !== null;

        if (!in_array($type, [
            'all',
            'salon',
            'barber',
        ], true)) {
            $type = 'all';
        }

        if (!in_array($sort, [
            'recommended',
            'rating',
            'newest',
            'nearest',
        ], true)) {
            $sort = 'recommended';
        }

        if (
            $sort === 'nearest' &&
            !$hasLocation
        ) {
            $sort = 'recommended';
        }


        /*
        |--------------------------------------------------------------------------
        | Main Salon Results
        |--------------------------------------------------------------------------
        */

        $salonQuery = Salon::query()
            ->where('is_active', true)

            ->withCount([
                'barbers' => fn ($q) =>
                $q->where('is_active', true),

                'services' => fn ($q) =>
                $q->where('is_active', true),

                'reviews' => fn ($q) =>
                $q->where('is_published', true),
            ])

            ->withAvg([
                'reviews' => fn ($q) =>
                $q->where('is_published', true),
            ], 'rating')

            ->with([
                'portfolioItems' => fn ($q) =>
                $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->limit(4),
            ]);


        if ($type === 'barber') {
            $salonQuery->whereRaw('1 = 0');
        } else {
            $this->applySalonSearch(
                $salonQuery,
                $query,
                $code,
                $city
            );
        }


        if ($hasLocation) {
            $salonQuery
                ->select('salons.*')
                ->selectRaw(
                    $this->distanceSql() .
                    ' AS distance_km',
                    [
                        $latitude,
                        $latitude,
                        $longitude,
                    ]
                );
        }


        match ($sort) {

            'nearest' =>
            $salonQuery
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance_km')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count'),

            'rating' =>
            $salonQuery
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count')
                ->latest('id'),

            'newest' =>
            $salonQuery->latest('id'),

            default =>
            $salonQuery
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count')
                ->latest('id'),
        };


        $salons = $salonQuery
            ->paginate(12)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Popular Barbers
        |--------------------------------------------------------------------------
        */

        $barberQuery = Barber::query()
            ->where('is_active', true)

            ->whereHas(
                'salon',
                function ($q) use ($city) {

                    $q->where('is_active', true);

                    if ($city !== '') {
                        $q->where('city', $city);
                    }
                }
            )

            ->with([
                'salon' => function ($q) {

                    $q
                        ->withAvg([
                            'reviews' => fn ($r) =>
                            $r->where('is_published', true),
                        ], 'rating')

                        ->withCount([
                            'reviews' => fn ($r) =>
                            $r->where('is_published', true),
                        ]);
                },
            ])

            ->withCount([
                'bookings as completed_bookings_count' =>
                    fn ($q) =>
                    $q->where(
                        'status',
                        BookingStatus::COMPLETED->value
                    ),
            ]);


        if ($query !== '') {

            $like = '%' . $query . '%';

            $barberQuery->where(
                function ($q) use ($like) {

                    $q
                        ->where(
                            'name',
                            'like',
                            $like
                        )

                        ->orWhere(
                            'specialty',
                            'like',
                            $like
                        )

                        ->orWhere(
                            'bio',
                            'like',
                            $like
                        );
                }
            );
        }


        $barbers = $barberQuery
            ->orderByDesc('completed_bookings_count')
            ->latest('id')
            ->limit(12)
            ->get();


        if ($hasLocation) {

            $barbers = $barbers
                ->map(
                    function ($barber) use (
                        $latitude,
                        $longitude
                    ) {

                        $salon = $barber->salon;

                        $barber->distance_km =
                            $salon &&
                            $salon->latitude !== null &&
                            $salon->longitude !== null

                                ? $this->distanceInKm(
                                $latitude,
                                $longitude,
                                (float) $salon->latitude,
                                (float) $salon->longitude
                            )

                                : null;

                        return $barber;
                    }
                );

            if ($sort === 'nearest') {
                $barbers = $barbers
                    ->sortBy(
                        fn ($barber) =>
                            $barber->distance_km
                            ??
                            PHP_FLOAT_MAX
                    )
                    ->values();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Featured Salons
        |--------------------------------------------------------------------------
        */

        $featuredSalons = Salon::query()
            ->where('is_active', true)

            ->withAvg([
                'reviews' => fn ($q) =>
                $q->where('is_published', true),
            ], 'rating')

            ->withCount([
                'reviews' => fn ($q) =>
                $q->where('is_published', true),
            ])

            ->with([
                'portfolioItems' => fn ($q) =>
                $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->latest('id'),
            ])

            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->latest('id')
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = [
            [
                'title' => 'مو',
                'subtitle' => 'کوتاهی، رنگ و کراتین',
                'query' => 'مو',
            ],
            [
                'title' => 'ناخن',
                'subtitle' => 'مانیکور و پدیکور',
                'query' => 'ناخن',
            ],
            [
                'title' => 'میکاپ',
                'subtitle' => 'میکاپ و شینیون',
                'query' => 'میکاپ',
            ],
            [
                'title' => 'پوست',
                'subtitle' => 'فیشال و مراقبت',
                'query' => 'پوست',
            ],
            [
                'title' => 'ابرو',
                'subtitle' => 'اصلاح و فرم‌دهی',
                'query' => 'ابرو',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        $cities = Salon::query()
            ->where('is_active', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');


        /*
        |--------------------------------------------------------------------------
        | Real Available Slots Preview
        |--------------------------------------------------------------------------
        */

        $availableSlots = collect();

        if ($type !== 'barber') {

            $candidates = Salon::query()
                ->where('is_active', true)

                ->when(
                    $city !== '',
                    fn ($q) =>
                    $q->where('city', $city)
                )

                ->with([
                    'barbers' => fn ($q) =>
                    $q
                        ->where('is_active', true)
                        ->orderBy('id'),

                    'services' => fn ($q) =>
                    $q
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id'),
                ])

                ->when(
                    $hasLocation,
                    function ($q) use (
                        $latitude,
                        $longitude
                    ) {

                        $q
                            ->select('salons.*')
                            ->selectRaw(
                                $this->distanceSql() .
                                ' AS distance_km',
                                [
                                    $latitude,
                                    $latitude,
                                    $longitude,
                                ]
                            )
                            ->orderBy('distance_km');
                    },
                    fn ($q) => $q->latest('id')
                )

                ->limit(6)
                ->get();


            foreach ($candidates as $salon) {

                $barber = $salon->barbers->first();
                $service = $salon->services->first();

                if (!$barber || !$service) {
                    continue;
                }


                foreach (range(0, 2) as $offset) {

                    $date = Carbon::today()
                        ->addDays($offset);

                    $slots = $availabilityService->slots(
                        $salon,
                        $barber,
                        $service,
                        $date
                    );

                    $slot = collect($slots)
                        ->firstWhere(
                            'available',
                            true
                        );

                    if ($slot) {

                        $availableSlots->push([
                            'salon' => $salon,
                            'barber' => $barber,
                            'service' => $service,
                            'slot' => $slot,
                            'date' => $date->copy(),
                        ]);

                        break;
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Map
        |--------------------------------------------------------------------------
        */

        $mapQuery = Salon::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        $this->applySalonSearch(
            $mapQuery,
            $query,
            $code,
            $city
        );


        if ($hasLocation) {

            $mapQuery
                ->select([
                    'salons.id',
                    'salons.name',
                    'salons.slug',
                    'salons.latitude',
                    'salons.longitude',
                    'salons.city',
                    'salons.district',
                ])
                ->selectRaw(
                    $this->distanceSql() .
                    ' AS distance_km',
                    [
                        $latitude,
                        $latitude,
                        $longitude,
                    ]
                );

        } else {

            $mapQuery->select([
                'id',
                'name',
                'slug',
                'latitude',
                'longitude',
                'city',
                'district',
            ]);
        }


        if (
            $sort === 'nearest' &&
            $hasLocation
        ) {
            $mapQuery->orderBy(
                'distance_km'
            );
        } else {
            $mapQuery->latest('id');
        }


        $mapSalons = $mapQuery
            ->limit(50)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */

        $activeSalonCount =
            Salon::query()
                ->where('is_active', true)
                ->count();

        $activeBarberCount =
            Barber::query()
                ->where('is_active', true)
                ->whereHas(
                    'salon',
                    fn ($q) =>
                    $q->where('is_active', true)
                )
                ->count();

        $publishedReviewCount =
            Review::query()
                ->where('is_published', true)
                ->count();


        /*
        |--------------------------------------------------------------------------
        | SEO
        |--------------------------------------------------------------------------
        */

        $hasSearch =
            $query !== '' ||
            $code !== '' ||
            $city !== '';

        $seoTitle = match (true) {

            $type === 'barber' =>
            'کشف آرایشگرها | RM نوبت‌دهی',

            $sort === 'nearest' =>
            'سالن‌های نزدیک من | RM نوبت‌دهی',

            $sort === 'rating' =>
            'محبوب‌ترین سالن‌ها | RM نوبت‌دهی',

            $hasSearch =>
            'جستجوی سالن و آرایشگر | RM نوبت‌دهی',

            default =>
            'کشف سالن و آرایشگر | RM نوبت‌دهی',
        };


        return view(
            'customer.discover',
            [
                'salons' => $salons,
                'barbers' => $barbers,
                'featuredSalons' => $featuredSalons,
                'availableSlots' => $availableSlots,
                'mapSalons' => $mapSalons,
                'cities' => $cities,
                'categories' => $categories,

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

                'seoDescription' =>
                    'سالن، آرایشگر یا خدمت موردنظرت را پیدا کن، نظر مشتری‌ها را ببین و آنلاین نوبت بگیر.',

                'robots' =>
                    $hasSearch
                        ? 'noindex,follow'
                        : 'index,follow',
            ]
        );
    }


    private function applySalonSearch(
        Builder $builder,
        string $query,
        string $code,
        string $city
    ): void {

        if ($query !== '') {

            $like = '%' . $query . '%';

            $builder->where(
                function ($q) use ($like) {

                    $q
                        ->where('name', 'like', $like)
                        ->orWhere(
                            'description',
                            'like',
                            $like
                        )
                        ->orWhere(
                            'city',
                            'like',
                            $like
                        )
                        ->orWhere(
                            'district',
                            'like',
                            $like
                        )
                        ->orWhere(
                            'address',
                            'like',
                            $like
                        )
                        ->orWhere(
                            'code',
                            'like',
                            $like
                        )
                        ->orWhereHas(
                            'services',
                            function ($service) use (
                                $like
                            ) {

                                $service
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->where(
                                        'name',
                                        'like',
                                        $like
                                    );
                            }
                        );
                }
            );
        }


        if ($code !== '') {
            $builder->where(
                'code',
                $code
            );
        }


        if ($city !== '') {
            $builder->where(
                'city',
                $city
            );
        }
    }


    private function distanceSql(): string
    {
        return <<<'SQL'
6371 * 2 * ASIN(
    SQRT(
        POWER(
            SIN(
                RADIANS(
                    salons.latitude - ?
                ) / 2
            ),
            2
        )
        +
        COS(RADIANS(?))
        *
        COS(RADIANS(salons.latitude))
        *
        POWER(
            SIN(
                RADIANS(
                    salons.longitude - ?
                ) / 2
            ),
            2
        )
    )
)
SQL;
    }


    private function nullableFloat(
        mixed $value
    ): ?float {

        if (
            $value === null ||
            $value === '' ||
            !is_numeric($value)
        ) {
            return null;
        }

        $value = (float) $value;

        return is_finite($value)
            ? $value
            : null;
    }


    private function distanceInKm(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): float {

        $earthRadius = 6371;

        $latDelta =
            deg2rad(
                $latitude2 -
                $latitude1
            );

        $lngDelta =
            deg2rad(
                $longitude2 -
                $longitude1
            );

        $a =
            sin(
                $latDelta / 2
            ) ** 2

            +

            cos(
                deg2rad(
                    $latitude1
                )
            )

            *

            cos(
                deg2rad(
                    $latitude2
                )
            )

            *

            sin(
                $lngDelta / 2
            ) ** 2;

        $c =
            2 *
            atan2(
                sqrt($a),
                sqrt(1 - $a)
            );

        return $earthRadius * $c;
    }
}
