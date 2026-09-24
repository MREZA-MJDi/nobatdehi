<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\Service;
use App\Services\Booking\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability
    ) {
    }

    private const PER_PAGE = 12;

    private const POPULAR_SALONS_LIMIT = 6;

    private const CARD_SERVICES_LIMIT = 3;

    private const SERVICE_OPTIONS_LIMIT = 40;

    private const DEFAULT_RADIUS_KM = 15.0;

    private const MAX_RADIUS_KM = 100.0;

    private const CACHE_POPULAR_SECONDS = 120;

    private const CACHE_OPTIONS_SECONDS = 600;

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
            $this->applyHasAvailableSlotToday(
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

        $this->attachCardServices($salons);

        /*
        |--------------------------------------------------------------------------
        | Filter options
        |--------------------------------------------------------------------------
        */

        $serviceOptions = Cache::remember(
            'discover:service-options:v3',
            now()->addSeconds(self::CACHE_OPTIONS_SECONDS),
            fn () => Service::query()
                ->where('is_active', true)
                ->whereHas('salon', function ($query) {
                    $query->where('is_active', true);
                })
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->selectRaw('TRIM(name) AS name')
                ->distinct()
                ->orderBy('name')
                ->limit(self::SERVICE_OPTIONS_LIMIT)
                ->pluck('name')
        );

        $provinces = $this->provinces();

        $cities = $this->cities(
            $filters['province']
        );

        /*
        |--------------------------------------------------------------------------
        | AJAX result response
        |--------------------------------------------------------------------------
        */

        if ($request->ajax()) {
            return view(
                'customer.discover.partials.dynamic',
                compact(
                    'salons',
                    'serviceOptions',
                    'provinces',
                    'cities',
                    'filters',
                    'hasGeo'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Popular salons
        |--------------------------------------------------------------------------
        */

        $popularSalons = Cache::remember(
            'discover:popular-salons:v3',
            now()->addSeconds(self::CACHE_POPULAR_SECONDS),
            function () {
                $items = $this->baseSalonQuery()
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('reviews_count')
                    ->latest('id')
                    ->limit(self::POPULAR_SALONS_LIMIT)
                    ->get();

                $this->attachCardServices($items);

                return $items;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Featured salon
        |--------------------------------------------------------------------------
        */

        $featuredSalon = $popularSalons->first();

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
                'featuredSalon',
                'serviceOptions',
                'provinces',
                'cities',
                'filters',
                'hasGeo',
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
            ->select([
                'id',
                'name',
                'slug',
                'code',
                'province',
                'city',
                'district',
                'address',
                'latitude',
                'longitude',
                'cover_path',
                'logo_path',
                'is_active',
            ])
            ->where('is_active', true)
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
;
    }

    /**
     * Load only the small service preview each salon card needs.
     * This avoids eager-loading every active service for every salon.
     */
    private function attachCardServices($items): void
    {
        $collection = method_exists($items, 'getCollection')
            ? $items->getCollection()
            : $items;

        $salonIds = $collection
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($salonIds->isEmpty()) {
            return;
        }

        $servicesBySalon = Service::query()
            ->whereIn('salon_id', $salonIds)
            ->where('is_active', true)
            ->select([
                'id',
                'salon_id',
                'name',
                'price',
                'sort_order',
                'is_active',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('salon_id');

        foreach ($collection as $salon) {
            $salon->setRelation(
                'services',
                collect($servicesBySalon->get($salon->id, []))
                    ->take(self::CARD_SERVICES_LIMIT)
                    ->values()
            );
        }
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
    | Actual availability today
    |--------------------------------------------------------------------------
    |
    | "today" means the salon has at least one genuinely bookable
    | slot today for an active barber and an active service.
    |
    | We first reduce the candidate salons using the already-built
    | Discover query, then evaluate the real booking availability for
    | those candidates with the same AvailabilityService used by Booking.
    |--------------------------------------------------------------------------
    */

    private function applyHasAvailableSlotToday(
        $query
    ): void {
        $timezone = config('app.timezone', 'Asia/Tehran');
        $today = Carbon::now($timezone)->startOfDay();
        $todayString = $today->toDateString();
        $dayOfWeek = ($today->dayOfWeek + 1) % 7;

        /*
        |--------------------------------------------------------------------------
        | Cheap candidate reduction first.
        |--------------------------------------------------------------------------
        */

        $query
            ->whereDoesntHave(
                'dailyStatuses',
                function ($relation) use ($todayString) {
                    $relation
                        ->whereDate('date', $todayString)
                        ->where('is_closed', true);
                }
            )
            ->whereHas(
                'workingHours',
                function ($relation) use ($dayOfWeek) {
                    $relation
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_closed', false)
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time');
                }
            )
            ->whereHas(
                'services',
                fn ($relation) => $relation->where('is_active', true)
            )
            ->whereHas(
                'barbers',
                fn ($relation) => $relation->where('is_active', true)
            );

        $candidateIds = (clone $query)
            ->reorder()
            ->select('salons.id')
            ->distinct()
            ->pluck('salons.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($candidateIds->isEmpty()) {
            $query->whereIn('salons.id', [0]);
            return;
        }

        $candidateSalons = Salon::query()
            ->whereIn('id', $candidateIds)
            ->with([
                'workingHours',
                'dailyStatuses' => function ($relation) use ($todayString) {
                    $relation->whereDate('date', $todayString);
                },
                'services' => function ($relation) {
                    $relation
                        ->where('is_active', true)
                        ->orderBy('duration_minutes')
                        ->orderBy('id');
                },
                'barbers' => function ($relation) {
                    $relation
                        ->where('is_active', true)
                        ->orderBy('id');
                },

                'barbers.bookings' => function ($relation) use ($todayString) {
                    $relation
                        ->whereDate('booking_date', $todayString)
                        ->whereIn(
                            'status',
                            collect(\App\Enums\BookingStatus::cases())
                                ->filter(
                                    fn (\App\Enums\BookingStatus $status): bool =>
                                        $status->blocksAvailability()
                                )
                                ->map(
                                    fn (\App\Enums\BookingStatus $status): string =>
                                        $status->value
                                )
                                ->all()
                        )
                        ->select([
                            'id',
                            'barber_id',
                            'booking_date',
                            'start_time',
                            'end_time',
                            'status',
                        ]);
                },
            ])
            ->get();

        $availableSalonIds = $candidateSalons
            ->filter(fn (Salon $salon): bool =>
                $this->salonHasAvailableSlotToday(
                    $salon,
                    $today
                )
            )
            ->pluck('id')
            ->values();

        if ($availableSalonIds->isEmpty()) {
            $query->whereIn('salons.id', [0]);
            return;
        }

        $query->whereIn(
            'salons.id',
            $availableSalonIds->all()
        );
    }

    private function salonHasAvailableSlotToday(
        Salon $salon,
        Carbon $today
    ): bool {
        if (! $salon->is_active) {
            return false;
        }

        $dailyStatus = $salon->dailyStatuses->first();

        if ($dailyStatus?->is_closed) {
            return false;
        }

        $hasWorkingHours = $salon->workingHours->contains(
            fn ($workingHour): bool =>
                ! $workingHour->is_closed &&
                $workingHour->start_time &&
                $workingHour->end_time
        );

        if (
            ! $hasWorkingHours ||
            $salon->barbers->isEmpty() ||
            $salon->services->isEmpty()
        ) {
            return false;
        }

        /*
        | The shortest active service is sufficient to answer the
        | existence question: if even the shortest service has no
        | free start time for a barber, no longer service can fit.
        */
        $shortestService = $salon->services->first();

        foreach ($salon->barbers as $barber) {
            $slots = $this->availability->slots(
                $salon,
                $barber,
                $shortestService,
                $today
            );

            if (collect($slots)->contains(
                fn (array $slot): bool =>
                    (bool) ($slot['available'] ?? false)
            )) {
                return true;
            }
        }

        return false;
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
            max(0.1, $radius)
        );

        /*
        |--------------------------------------------------------------------------
        | Bounding box
        |--------------------------------------------------------------------------
        |
        | Reduce candidate rows before the Haversine calculation.
        |--------------------------------------------------------------------------
        */

        $latDelta = $radius / 111.045;

        $cosLatitude = max(
            0.01,
            abs(cos(deg2rad($lat)))
        );

        $lngDelta = min(
            180.0,
            $radius / (111.045 * $cosLatitude)
        );

        $minLat = max(-90.0, $lat - $latDelta);
        $maxLat = min(90.0, $lat + $latDelta);
        $minLng = $lng - $lngDelta;
        $maxLng = $lng + $lngDelta;

        $query
            ->whereNotNull('salons.latitude')
            ->whereNotNull('salons.longitude')
            ->whereBetween(
                'salons.latitude',
                [$minLat, $maxLat]
            );

        if ($minLng >= -180.0 && $maxLng <= 180.0) {
            $query->whereBetween(
                'salons.longitude',
                [$minLng, $maxLng]
            );
        } else {
            $query->where(function ($query) use (
                $minLng,
                $maxLng
            ) {
                if ($minLng < -180.0) {
                    $wrappedMin = $minLng + 360.0;

                    $query
                        ->whereBetween(
                            'salons.longitude',
                            [-180.0, $maxLng]
                        )
                        ->orWhereBetween(
                            'salons.longitude',
                            [$wrappedMin, 180.0]
                        );

                    return;
                }

                $wrappedMax = $maxLng - 360.0;

                $query
                    ->whereBetween(
                        'salons.longitude',
                        [$minLng, 180.0]
                    )
                    ->orWhereBetween(
                        'salons.longitude',
                        [-180.0, $wrappedMax]
                    );
            });
        }

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
                        ) AS distance_km"
                )
            )
            ->having('distance_km', '<=', $radius);
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
    | Provinces
    |--------------------------------------------------------------------------
    */

    private function provinces()
    {
        return Cache::remember(
            'discover:provinces:v3',
            now()->addSeconds(self::CACHE_OPTIONS_SECONDS),
            fn () => Salon::query()
                ->where('is_active', true)
                ->whereNotNull('province')
                ->where('province', '!=', '')
                ->select('province')
                ->distinct()
                ->orderBy('province')
                ->pluck('province')
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
        $cacheKey = 'discover:cities:v3:' . (
            $province !== null && $province !== ''
                ? sha1($province)
                : 'all'
        );

        return Cache::remember(
            $cacheKey,
            now()->addSeconds(self::CACHE_OPTIONS_SECONDS),
            fn () => Salon::query()
                ->where('is_active', true)
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->when(
                    $province !== null && $province !== '',
                    fn ($query) => $query->where('province', $province)
                )
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
        );
    }
}