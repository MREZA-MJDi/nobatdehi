<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    private const PER_PAGE = 12;

    private const NEARBY_LIMIT = 6;

    private const POPULAR_SALONS_LIMIT = 6;

    private const POPULAR_SERVICES_LIMIT = 8;

    private const STYLIST_LIMIT = 18;

    private const CARD_SERVICES_LIMIT = 3;

    private const SERVICE_OPTIONS_LIMIT = 40;

    private const DEFAULT_RADIUS_KM = 15.0;

    private const MAX_RADIUS_KM = 100.0;

    private const NEARBY_RADII = [
        2.0,
        5.0,
        10.0,
        15.0,
        25.0,
        50.0,
        100.0,
    ];

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = $this->extractFilters($request);

        $hasGeo = $this->hasGeo($filters);

        /*
        |--------------------------------------------------------------------------
        | Search mode
        |--------------------------------------------------------------------------
        */

        $isSearchMode =
            $filters['q'] !== ''
            || $filters['type'] !== ''
            || $filters['service'] !== null
            || $filters['province'] !== ''
            || $filters['city'] !== ''
            || $filters['location'] !== ''
            || $filters['district'] !== ''
            || (float) $filters['min_rating'] > 0
            || (
                is_numeric($filters['price_max'])
                && (float) $filters['price_max'] > 0
            )
            || $filters['open_now']
            || $filters['today']
            || $hasGeo
            || $filters['sort'] !== 'recommended';

        /*
        |--------------------------------------------------------------------------
        | Main salon query
        |--------------------------------------------------------------------------
        */

        $salonQuery = $this->baseSalonQuery();

        $this->applySearch(
            $salonQuery,
            $filters['q']
        );

        $this->applyTypeFilter(
            $salonQuery,
            $filters['type']
        );

        $this->applyServiceFilter(
            $salonQuery,
            $filters['service']
        );

        $this->applyRating(
            $salonQuery,
            $filters['min_rating']
        );

        $this->applyPriceCap(
            $salonQuery,
            $filters['price_max']
        );

        /*
        |--------------------------------------------------------------------------
        | Location filters
        |--------------------------------------------------------------------------
        */

        if ($filters['province'] !== '') {
            $salonQuery->where(
                'province',
                $filters['province']
            );
        }

        if ($filters['city'] !== '') {
            $salonQuery->where(
                'city',
                $filters['city']
            );
        }

        if ($filters['location'] !== '') {
            $locationLike = '%' . $filters['location'] . '%';

            $salonQuery->where(function ($query) use ($locationLike) {
                $query
                    ->where('province', 'like', $locationLike)
                    ->orWhere('city', 'like', $locationLike)
                    ->orWhere('district', 'like', $locationLike)
                    ->orWhere('address', 'like', $locationLike);
            });
        }

        if ($filters['district'] !== '') {
            $salonQuery->where(
                'district',
                $filters['district']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Availability filters
        |--------------------------------------------------------------------------
        */

        if ($filters['open_now']) {
            $this->applyOpenNow(
                $salonQuery
            );
        }

        if ($filters['today']) {
            $this->applyHasSlotToday(
                $salonQuery
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Geo
        |--------------------------------------------------------------------------
        */

        if ($hasGeo) {
            $this->applyGeo(
                $salonQuery,
                $filters
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Price sorting
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $filters['sort'],
                [
                    'price_asc',
                    'price_desc',
                ],
                true
            )
        ) {
            $this->applyMinimumServicePrice(
                $salonQuery
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $this->applySorting(
            $salonQuery,
            $filters['sort'],
            $hasGeo
        );

        /*
        |--------------------------------------------------------------------------
        | Main paginated results
        |--------------------------------------------------------------------------
        */

        $salons = $salonQuery
            ->paginate(
                self::PER_PAGE
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Popular salons
        |
        | مهم:
        | این جدا از $salons است تا Viewهای Discover
        | pagination اصلی را خراب نکنند.
        |--------------------------------------------------------------------------
        */

        $popularSalons = $this->baseSalonQuery()
            ->orderByDesc(
                'reviews_avg_rating'
            )
            ->orderByDesc(
                'reviews_count'
            )
            ->latest('id')
            ->limit(
                self::POPULAR_SALONS_LIMIT
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Featured salon
        |--------------------------------------------------------------------------
        */

        $featuredSalon = $this->baseSalonQuery()
            ->orderByDesc(
                'reviews_avg_rating'
            )
            ->orderByDesc(
                'reviews_count'
            )
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Nearby salons
        |--------------------------------------------------------------------------
        */

        $nearbySalons = collect();

        $nearbyRadius = null;

        if ($hasGeo) {
            $nearbyData = $this->nearbySalons(
                $filters
            );

            $nearbySalons = $nearbyData['items'];

            $nearbyRadius = $nearbyData['radius'];
        }

        /*
        |--------------------------------------------------------------------------
        | Service options
        |
        | برای select فیلترها
        |--------------------------------------------------------------------------
        */

        $serviceOptions = Service::query()
            ->where(
                'is_active',
                true
            )
            ->whereHas(
                'salon',
                function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                }
            )
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(self::SERVICE_OPTIONS_LIMIT)
            ->pluck('name');

        /*
        |--------------------------------------------------------------------------
        | Popular services
        |--------------------------------------------------------------------------
        */

        $popularServices = Service::query()
            ->where(
                'is_active',
                true
            )
            ->whereHas(
                'salon',
                function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                }
            )
            ->select([
                'id',
                'salon_id',
                'name',
                'description',
                'duration_minutes',
                'price',
                'image_path',
            ])
            ->with([
                'salon:id,name,slug,code,city,district',
            ])
            ->withCount([
                'bookings' => function ($query) {
                    $query->where(
                        'status',
                        '!=',
                        'cancelled'
                    );
                },
            ])
            ->orderByDesc(
                'bookings_count'
            )
            ->latest('id')
            ->limit(
                self::POPULAR_SERVICES_LIMIT
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Stylists
        |--------------------------------------------------------------------------
        */

        $stylists = Barber::query()
            ->where(
                'is_active',
                true
            )
            ->whereHas(
                'salon',
                function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                }
            )
            ->with([
                'salon:id,name,slug,code,city,district',
            ])
            ->select([
                'id',
                'salon_id',
                'name',
                'specialty',
                'bio',
                'image_path',
            ])
            ->withCount([
                'bookings' => function ($query) {
                    $query->where(
                        'status',
                        '!=',
                        'cancelled'
                    );
                },
            ])
            ->orderByDesc(
                'bookings_count'
            )
            ->latest('id')
            ->limit(
                self::STYLIST_LIMIT
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Service categories
        |--------------------------------------------------------------------------
        */

        $serviceCategories = $this->serviceCategories();

        /*
        |--------------------------------------------------------------------------
        | Provinces
        |--------------------------------------------------------------------------
        */

        $provinces = $this->provinces();

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        $cities = $this->cities(
            $filters['province']
        );

        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */

        $stats = [
            'salons' => Salon::query()
                ->where(
                    'is_active',
                    true
                )
                ->count(),

            'barbers' => Barber::query()
                ->where(
                    'is_active',
                    true
                )
                ->count(),

            'services' => Service::query()
                ->where(
                    'is_active',
                    true
                )
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.discover',
            compact(
                'salons',
                'popularSalons',
                'nearbySalons',
                'nearbyRadius',
                'featuredSalon',
                'popularServices',
                'serviceOptions',
                'stylists',
                'serviceCategories',
                'provinces',
                'cities',
                'stats',
                'filters',
                'hasGeo',
                'isSearchMode',
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Filter extraction
    |--------------------------------------------------------------------------
    */

    private function extractFilters(
        Request $request
    ): array {
        $minRating = (float) $request->query(
            'min_rating',
            0
        );

        $minRating = max(
            0,
            min(
                5,
                $minRating
            )
        );

        $radius = (float) $request->query(
            'radius',
            self::DEFAULT_RADIUS_KM
        );

        if ($radius <= 0) {
            $radius = self::DEFAULT_RADIUS_KM;
        }

        $radius = min(
            self::MAX_RADIUS_KM,
            $radius
        );

        $sort = (string) $request->query(
            'sort',
            'recommended'
        );

        $allowedSorts = [
            'recommended',
            'rating',
            'price_asc',
            'price_desc',
            'distance',
            'newest',
        ];

        if (
            ! in_array(
                $sort,
                $allowedSorts,
                true
            )
        ) {
            $sort = 'recommended';
        }

        $service = $request->query(
            'service'
        );

        if (
            $service === ''
        ) {
            $service = null;
        }

        return [
            'q' => trim(
                (string) $request->query(
                    'q',
                    ''
                )
            ),

            'type' => trim(
                (string) $request->query(
                    'type',
                    ''
                )
            ),

            'service' => $service,

            'province' => trim(
                (string) $request->query(
                    'province',
                    ''
                )
            ),

            'city' => trim(
                (string) $request->query(
                    'city',
                    ''
                )
            ),

            'location' => trim(
                (string) $request->query(
                    'location',
                    ''
                )
            ),

            'district' => trim(
                (string) $request->query(
                    'district',
                    ''
                )
            ),

            'sort' => $sort,

            'min_rating' => $minRating,

            'price_max' => $request->query(
                'price_max'
            ),

            'open_now' => $request->boolean(
                'open_now'
            ),

            'today' => $request->boolean(
                'today'
            ),

            'lat' => $request->query(
                'lat'
            ),

            'lng' => $request->query(
                'lng'
            ),

            'radius' => $radius,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Geo check
    |--------------------------------------------------------------------------
    */

    private function hasGeo(
        array $filters
    ): bool {
        if (
            ! is_numeric(
                $filters['lat']
            )
            ||
            ! is_numeric(
                $filters['lng']
            )
        ) {
            return false;
        }

        $lat = (float) $filters['lat'];

        $lng = (float) $filters['lng'];

        return $lat >= -90
            && $lat <= 90
            && $lng >= -180
            && $lng <= 180;
    }

    /*
    |--------------------------------------------------------------------------
    | Base salon query
    |--------------------------------------------------------------------------
    */

    private function baseSalonQuery()
    {
        return Salon::query()
            ->where(
                'is_active',
                true
            )

            ->withCount([
                'services' => function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                },

                'barbers' => function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                },

                'reviews' => function ($query) {
                    $query->where(
                        'is_published',
                        true
                    );
                },
            ])

            ->withAvg([
                'reviews' => function ($query) {
                    $query->where(
                        'is_published',
                        true
                    );
                },
            ], 'rating')

            /*
             * عمداً limit روی relation نذاشتیم.
             * View خودش take(3) می‌کند.
             */
            ->with([
                'services' => function ($query) {
                    $query
                        ->where(
                            'is_active',
                            true
                        )
                        ->select([
                            'id',
                            'salon_id',
                            'name',
                            'price',
                            'sort_order',
                            'is_active',
                        ])
                        ->orderBy(
                            'sort_order'
                        )
                        ->orderBy(
                            'name'
                        );
                },
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    private function normalizeSearchTerm(string $value): string
    {
        $value = trim(
            preg_replace('/\\s+/u', ' ', $value) ?? $value
        );

        return str_replace(
            [
                'ي',
                'ى',
                'ك',
                'ة',
                'ۀ',
            ],
            [
                'ی',
                'ی',
                'ک',
                'ه',
                'ه',
            ],
            $value
        );
    }

    private function applySearch(
        $query,
        string $search
    ): void {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $terms = collect([
            $search,
            $this->normalizeSearchTerm($search),
        ])
            ->map(fn ($term) => trim((string) $term))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $query->where(function ($query) use ($terms) {
            foreach ($terms as $index => $term) {
                $searchLike = '%' . $term . '%';

                $method = $index === 0
                    ? 'where'
                    : 'orWhere';

                $query->{$method}(function ($query) use ($searchLike) {
                    $query
                        ->where('name', 'like', $searchLike)
                        ->orWhere('code', 'like', $searchLike)
                        ->orWhere('city', 'like', $searchLike)
                        ->orWhere('district', 'like', $searchLike)
                        ->orWhere('province', 'like', $searchLike)
                        ->orWhere('address', 'like', $searchLike)
                        ->orWhereHas(
                            'services',
                            function ($query) use ($searchLike) {
                                $query
                                    ->where('is_active', true)
                                    ->where(function ($query) use ($searchLike) {
                                        $query
                                            ->where('name', 'like', $searchLike)
                                            ->orWhere('description', 'like', $searchLike);
                                    });
                            }
                        )
                        ->orWhereHas(
                            'barbers',
                            function ($query) use ($searchLike) {
                                $query
                                    ->where('is_active', true)
                                    ->where(function ($query) use ($searchLike) {
                                        $query
                                            ->where('name', 'like', $searchLike)
                                            ->orWhere('specialty', 'like', $searchLike)
                                            ->orWhere('bio', 'like', $searchLike);
                                    });
                            }
                        );
                });
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Type
    |--------------------------------------------------------------------------
    |
    | type=salon:
    | نتیجه باید سالن فعال باشد.
    |
    | type=barber:
    | سالن باید حداقل یک متخصص فعال داشته باشد.
    |
    | چون خروجی Discover همچنان salon card است.
    |--------------------------------------------------------------------------
    */

    private function applyTypeFilter(
        $query,
        string $type
    ): void {
        if ($type === '') {
            return;
        }

        if ($type === 'salon') {
            return;
        }

        if ($type === 'barber') {
            $query->whereHas(
                'barbers',
                function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                }
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Service filter
    |--------------------------------------------------------------------------
    */

    private function applyServiceFilter(
        $query,
        $service
    ): void {
        if (
            $service === null
            ||
            $service === ''
        ) {
            return;
        }

        $query->whereHas(
            'services',
            function ($query) use (
                $service
            ) {
                $query->where(
                    'is_active',
                    true
                );

                if (
                    is_numeric(
                        $service
                    )
                ) {
                    $query->where(
                        'id',
                        (int) $service
                    );

                    return;
                }

                $serviceName = $this->normalizeSearchTerm(
                    trim((string) $service)
                );

                $query->where(
                    function ($query) use ($serviceName, $service) {
                        $query
                            ->where('name', 'like', '%' . trim((string) $service) . '%')
                            ->orWhere('name', 'like', '%' . $serviceName . '%');
                    }
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Rating
    |--------------------------------------------------------------------------
    */

    private function applyRating(
        $query,
        float $minRating
    ): void {
        if ($minRating <= 0) {
            return;
        }

        $query->whereRaw(
            '(SELECT AVG(reviews.rating) FROM reviews WHERE reviews.salon_id = salons.id AND reviews.is_published = 1) >= ?',
            [$minRating]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Minimum service price
    |--------------------------------------------------------------------------
    */

    private function applyMinimumServicePrice(
        $query
    ): void {
        $query->withMin(
            [
                'services as min_price' => function (
                    $query
                ) {
                    $query->where(
                        'is_active',
                        true
                    );
                },
            ],
            'price'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Price cap
    |--------------------------------------------------------------------------
    */

    private function applyPriceCap(
        $query,
        $priceMax
    ): void {
        if (
            ! is_numeric(
                $priceMax
            )
            ||
            (float) $priceMax <= 0
        ) {
            return;
        }

        $query->whereHas(
            'services',
            function ($query) use ($priceMax) {
                $query
                    ->where('is_active', true)
                    ->where('price', '<=', (int) $priceMax);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Open now
    |--------------------------------------------------------------------------
    */

    private function applyOpenNow(
        $query
    ): void {
        $now = Carbon::now();

        $dayOfWeek = ($now->dayOfWeek + 1) % 7;

        $previousDayOfWeek = $dayOfWeek === 0
            ? 6
            : $dayOfWeek - 1;

        $today = $now->toDateString();

        $time = $now->format(
            'H:i:s'
        );

        /*
        |--------------------------------------------------------------------------
        | Daily close override
        |--------------------------------------------------------------------------
        */

        $query->whereDoesntHave(
            'dailyStatuses',
            function ($query) use (
                $today
            ) {
                $query
                    ->where(
                        'date',
                        $today
                    )
                    ->where(
                        'is_closed',
                        true
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Working hours
        |--------------------------------------------------------------------------
        */

        $query->whereHas(
            'workingHours',
            function ($query) use (
                $dayOfWeek,
                $previousDayOfWeek,
                $time
            ) {
                $query->where(
                    function ($query) use (
                        $dayOfWeek,
                        $time
                    ) {
                        $query
                            ->where(
                                'day_of_week',
                                $dayOfWeek
                            )
                            ->where(
                                'is_closed',
                                false
                            )
                            ->whereNotNull('start_time')
                            ->whereNotNull('end_time')
                            ->whereColumn(
                                'start_time',
                                '<=',
                                'end_time'
                            )
                            ->whereTime(
                                'start_time',
                                '<=',
                                $time
                            )
                            ->whereTime(
                                'end_time',
                                '>=',
                                $time
                            );
                    }
                )->orWhere(
                    function ($query) use (
                        $previousDayOfWeek,
                        $time
                    ) {
                        $query
                            ->where(
                                'day_of_week',
                                $previousDayOfWeek
                            )
                            ->where(
                                'is_closed',
                                false
                            )
                            ->whereNotNull('start_time')
                            ->whereNotNull('end_time')
                            ->whereColumn(
                                'start_time',
                                '>',
                                'end_time'
                            )
                            ->where(
                                function ($query) use (
                                    $time
                                ) {
                                    $query
                                        ->whereTime(
                                            'start_time',
                                            '<=',
                                            $time
                                        )
                                        ->orWhereTime(
                                            'end_time',
                                            '>=',
                                            $time
                                        );
                                }
                            );
                    }
                );
            }
        )
        ->whereHas(
            'barbers',
            fn ($query) => $query->where('is_active', true)
        )
        ->whereHas(
            'services',
            fn ($query) => $query->where('is_active', true)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Has slot today
    |--------------------------------------------------------------------------
    |
    | فعلاً تخمینی است.
    | یعنی:
    | امروز بسته نباشد + working hour فعال داشته باشد.
    |--------------------------------------------------------------------------
    */

    private function applyHasSlotToday(
        $query
    ): void {
        $now = Carbon::now();

        $dayOfWeek = ($now->dayOfWeek + 1) % 7;

        $today = $now->toDateString();

        $query

            ->whereDoesntHave(
                'dailyStatuses',
                function ($query) use (
                    $today
                ) {
                    $query
                        ->where(
                            'date',
                            $today
                        )
                        ->where(
                            'is_closed',
                            true
                        );
                }
            )

            ->whereHas(
                'workingHours',
                function ($query) use (
                    $dayOfWeek
                ) {
                    $query
                        ->where(
                            'day_of_week',
                            $dayOfWeek
                        )
                        ->where(
                            'is_closed',
                            false
                        )
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time');
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Geo query
    |--------------------------------------------------------------------------
    */

    private function applyGeo(
        $query,
        array $filters
    ): void {
        $lat = (float) $filters['lat'];

        $lng = (float) $filters['lng'];

        $radius = (float) (
        $filters['radius']
            ?: self::DEFAULT_RADIUS_KM
        );

        $radius = min(
            self::MAX_RADIUS_KM,
            max(
                0.1,
                $radius
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Haversine
        |--------------------------------------------------------------------------
        */

        $haversine = '
            (
                6371 * ACOS(
                    GREATEST(
                        -1,
                        LEAST(
                            1,
                            COS(RADIANS(?))
                            * COS(RADIANS(salons.latitude))
                            * COS(
                                RADIANS(salons.longitude)
                                - RADIANS(?)
                            )
                            + SIN(RADIANS(?))
                            * SIN(RADIANS(salons.latitude))
                        )
                    )
                )
            )
        ';

        /*
        |--------------------------------------------------------------------------
        | Distance
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Keep Eloquent aggregate columns while adding distance.
        |--------------------------------------------------------------------------
        */
        $query
            ->addSelect(
                DB::raw(
                    "(
                        6371 * ACOS(
                            GREATEST(
                                -1,
                                LEAST(
                                    1,
                                    COS(RADIANS(" . $lat . "))
                                    * COS(RADIANS(salons.latitude))
                                    * COS(
                                        RADIANS(salons.longitude)
                                        - RADIANS(" . $lng . ")
                                    )
                                    + SIN(RADIANS(" . $lat . "))
                                    * SIN(RADIANS(salons.latitude))
                                )
                            )
                        )
                    ) AS distance_km"
                )
            )

            ->whereNotNull(
                'salons.latitude'
            )

            ->whereNotNull(
                'salons.longitude'
            )

            ->having(
                'distance_km',
                '<=',
                $radius
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    private function applySorting(
        $query,
        string $sort,
        bool $hasGeo
    ): void {
        switch ($sort) {

            case 'rating':

                $query
                    ->orderByDesc(
                        'reviews_avg_rating'
                    )
                    ->orderByDesc(
                        'reviews_count'
                    )
                    ->latest('id');

                break;

            case 'price_asc':

                $query
                    ->orderBy(
                        'min_price'
                    )
                    ->orderByDesc(
                        'reviews_avg_rating'
                    )
                    ->latest('id');

                break;

            case 'price_desc':

                $query
                    ->orderByDesc(
                        'min_price'
                    )
                    ->orderByDesc(
                        'reviews_avg_rating'
                    )
                    ->latest('id');

                break;

            case 'distance':

                if ($hasGeo) {

                    $query
                        ->orderBy(
                            'distance_km'
                        )
                        ->orderByDesc(
                            'reviews_avg_rating'
                        );

                } else {

                    $query
                        ->orderByDesc(
                            'reviews_avg_rating'
                        )
                        ->orderByDesc(
                            'reviews_count'
                        )
                        ->latest('id');
                }

                break;

            case 'newest':

                $query->latest(
                    'id'
                );

                break;

            case 'recommended':
            default:

                $query
                    ->orderByDesc(
                        'reviews_avg_rating'
                    )
                    ->orderByDesc(
                        'reviews_count'
                    )
                    ->latest('id');

                break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Progressive Nearby
    |--------------------------------------------------------------------------
    |
    | radius=10:
    |
    | 2km -> 5km -> 10km
    |
    | radius=30:
    |
    | 2 -> 5 -> 10 -> 15 -> 25 -> 30
    |--------------------------------------------------------------------------
    */

    private function nearbySalons(
        array $filters
    ): array {
        $requestedRadius = min(
            self::MAX_RADIUS_KM,
            max(
                0.1,
                (float) $filters['radius']
            )
        );

        $radii = collect(
            self::NEARBY_RADII
        )

            ->filter(
                function (
                    $radius
                ) use (
                    $requestedRadius
                ) {
                    return $radius
                        < $requestedRadius;
                }
            )

            ->push(
                $requestedRadius
            )

            ->unique()

            ->sort()

            ->values();

        foreach (
            $radii as $radius
        ) {

            $nearbyFilters = $filters;

            $nearbyFilters['radius'] = $radius;

            $query = $this->baseSalonQuery();

            /*
            |--------------------------------------------------------------------------
            | Nearby barbers
            |--------------------------------------------------------------------------
            */

            $query->with([
                'barbers' => function (
                    $query
                ) {
                    $query
                        ->where(
                            'is_active',
                            true
                        )
                        ->select([
                            'id',
                            'salon_id',
                            'name',
                            'specialty',
                            'image_path',
                        ])
                        ->orderBy(
                            'name'
                        );
                },
            ]);

            /*
            |--------------------------------------------------------------------------
            | Geo
            |--------------------------------------------------------------------------
            */

            $this->applyGeo(
                $query,
                $nearbyFilters
            );

            /*
            |--------------------------------------------------------------------------
            | Nearby ordering
            |--------------------------------------------------------------------------
            */

            $results = $query

                ->orderBy(
                    'distance_km'
                )

                ->orderByDesc(
                    'reviews_avg_rating'
                )

                ->limit(
                    self::NEARBY_LIMIT
                )

                ->get();

            if (
                $results->isNotEmpty()
            ) {
                return [
                    'items' => $results,

                    'radius' => $radius,
                ];
            }
        }

        return [
            'items' => collect(),

            'radius' => $requestedRadius,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Service categories
    |--------------------------------------------------------------------------
    */

    private function serviceCategories(): array
    {
        return config(
            'services.categories',
            [
                [
                    'key' => 'haircut',
                    'label' => 'اصلاح و کوتاهی',
                    'icon' => 'scissors',
                ],
                [
                    'key' => 'color',
                    'label' => 'رنگ و مش',
                    'icon' => 'palette',
                ],
                [
                    'key' => 'keratin',
                    'label' => 'کراتین و احیا',
                    'icon' => 'sparkles',
                ],
                [
                    'key' => 'nails',
                    'label' => 'ناخن',
                    'icon' => 'hand',
                ],
                [
                    'key' => 'makeup',
                    'label' => 'میکاپ',
                    'icon' => 'brush',
                ],
                [
                    'key' => 'brows',
                    'label' => 'ابرو و مژه',
                    'icon' => 'eye',
                ],
                [
                    'key' => 'skin',
                    'label' => 'پوست',
                    'icon' => 'droplet',
                ],
                [
                    'key' => 'massage',
                    'label' => 'ماساژ',
                    'icon' => 'heart',
                ],
                [
                    'key' => 'bridal',
                    'label' => 'عروس',
                    'icon' => 'crown',
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Provinces
    |--------------------------------------------------------------------------
    */

    private function provinces()
    {
        return Salon::query()

            ->where(
                'is_active',
                true
            )

            ->whereNotNull(
                'province'
            )

            ->where(
                'province',
                '!=',
                ''
            )

            ->select(
                'province'
            )

            ->distinct()

            ->orderBy(
                'province'
            )

            ->pluck(
                'province'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cities
    |--------------------------------------------------------------------------
    */

    private function cities(
        ?string $province
    ) {
        return Salon::query()

            ->where(
                'is_active',
                true
            )

            ->whereNotNull(
                'city'
            )

            ->where(
                'city',
                '!=',
                ''
            )

            ->when(
                $province !== null
                && $province !== '',
                function (
                    $query
                ) use (
                    $province
                ) {
                    $query->where(
                        'province',
                        $province
                    );
                }
            )

            ->select(
                'city'
            )

            ->distinct()

            ->orderBy(
                'city'
            )

            ->pluck(
                'city'
            );
    }
}
