<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\SalonDailyStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    private const PER_PAGE            = 12;
    private const NEARBY_LIMIT        = 6;
    private const CARD_SERVICES_LIMIT = 3;
    private const DEFAULT_RADIUS_KM   = 15.0;

    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $hasGeo  = $this->hasGeo($filters);

        /* ------------------------------------------------------------------
         | Query اصلی
         * ------------------------------------------------------------------ */
        $salonQuery = $this->baseSalonQuery();

        $this->applySearch($salonQuery, $filters['q']);
        $this->applyServiceFilter($salonQuery, $filters['service']);
        $this->applyRating($salonQuery, $filters['min_rating']);
        $this->applyPriceCap($salonQuery, $filters['price_max']);

        if ($filters['province']) {
            $salonQuery->where('province', $filters['province']);
        }
        if ($filters['city']) {
            $salonQuery->where('city', $filters['city']);
        }
        if ($filters['district']) {
            $salonQuery->where('district', $filters['district']);
        }
        if ($filters['open_now']) {
            $this->applyOpenNow($salonQuery);
        }
        if ($filters['today']) {
            $this->applyHasSlotToday($salonQuery);
        }
        if ($hasGeo) {
            $this->applyGeo($salonQuery, $filters);
        }

        $this->applySorting($salonQuery, $filters['sort'], $hasGeo);

        $salons = $salonQuery
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        /* ------------------------------------------------------------------
         | Featured
         * ------------------------------------------------------------------ */
        $featuredSalon = (clone $this->baseSalonQuery())
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->latest('id')
            ->first();

        /* ------------------------------------------------------------------
         | Nearby
         * ------------------------------------------------------------------ */
        $nearbySalons = $this->nearbySalons($filters, $hasGeo);

        /* ------------------------------------------------------------------
         | Popular Services
         * ------------------------------------------------------------------ */
        $popularServices = Service::query()
            ->where('is_active', true)
            ->select([
                'id', 'salon_id', 'name', 'description',
                'duration_minutes', 'price', 'image_path',
            ])
            ->with(['salon:id,name,slug,code,city,district'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->latest('id')
            ->limit(8)
            ->get();

        /* ------------------------------------------------------------------
         | Stylists
         * ------------------------------------------------------------------ */
        $stylists = Barber::query()
            ->where('is_active', true)
            ->with(['salon:id,name,slug,code,city,district'])
            ->select(['id', 'salon_id', 'name', 'specialty', 'bio', 'image_path'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->latest('id')
            ->limit(8)
            ->get();

        /* ------------------------------------------------------------------
         | Helper data
         * ------------------------------------------------------------------ */
        $serviceCategories = $this->serviceCategories();
        $provinces         = $this->provinces();
        $cities            = $this->cities($filters['province']);

        /* ------------------------------------------------------------------
         | Stats
         * ------------------------------------------------------------------ */
        $stats = [
            'salons'   => Salon::where('is_active', true)->count(),
            'barbers'  => Barber::where('is_active', true)->count(),
            'services' => Service::where('is_active', true)->count(),
        ];

        return view('customer.discover', compact(
            'salons',
            'nearbySalons',
            'featuredSalon',
            'popularServices',
            'stylists',
            'serviceCategories',
            'provinces',
            'cities',
            'stats',
            'filters',
        ));
    }

    /* =====================================================================
     | Filter Extraction
     * ===================================================================== */

    private function extractFilters(Request $request): array
    {
        return [
            'q'          => trim((string) $request->query('q', '')),
            'service'    => $request->query('service'),
            'province'   => $request->query('province'),
            'city'       => $request->query('city'),
            'district'   => $request->query('district'),
            'sort'       => (string) $request->query('sort', 'recommended'),
            'min_rating' => (float)  $request->query('min_rating', 0),
            'price_max'  => $request->query('price_max'),
            'open_now'   => $request->boolean('open_now'),
            'today'      => $request->boolean('today'),
            'lat'        => $request->query('lat'),
            'lng'        => $request->query('lng'),
            'radius'     => (float) $request->query('radius', self::DEFAULT_RADIUS_KM),
            'gender'     => $request->query('gender'), // فعلاً UI-only
        ];
    }

    private function hasGeo(array $filters): bool
    {
        return is_numeric($filters['lat']) && is_numeric($filters['lng']);
    }

    /* =====================================================================
     | Base Query
     * ===================================================================== */

    private function baseSalonQuery(): Builder
    {
        return Salon::query()
            ->where('is_active', true)
            ->withCount([
                'services' => fn (Builder $q) => $q->where('is_active', true),
                'barbers'  => fn (Builder $q) => $q->where('is_active', true),
                'reviews',
            ])
            ->withAvg('reviews', 'rating')
            ->with(['services' => $this->servicesForCard()]);
    }

    private function servicesForCard(): \Closure
    {
        return function ($query) {
            $query
                ->where('is_active', true)
                ->select(['id', 'salon_id', 'name', 'price'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(self::CARD_SERVICES_LIMIT);
        };
    }

    /* =====================================================================
     | Filter Appliers
     * ===================================================================== */

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhereHas('services', function (Builder $sq) use ($search) {
                    $sq->where('is_active', true)
                        ->where(function (Builder $inner) use ($search) {
                            $inner->where('name', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                })
                ->orWhereHas('barbers', function (Builder $bq) use ($search) {
                    $bq->where('is_active', true)
                        ->where(function (Builder $inner) use ($search) {
                            $inner->where('name', 'like', "%{$search}%")
                                ->orWhere('specialty', 'like', "%{$search}%")
                                ->orWhere('bio', 'like', "%{$search}%");
                        });
                });
        });
    }

    private function applyServiceFilter(Builder $query, $service): void
    {
        if (! $service) {
            return;
        }

        $query->whereHas('services', function (Builder $q) use ($service) {
            $q->where('is_active', true);

            if (is_numeric($service)) {
                $q->where('id', $service);
            } else {
                $q->where('name', 'like', "%{$service}%");
            }
        });
    }

    private function applyRating(Builder $query, float $minRating): void
    {
        if ($minRating <= 0) {
            return;
        }
        $query->having('reviews_avg_rating', '>=', $minRating);
    }

    private function applyPriceCap(Builder $query, $priceMax): void
    {
        if (! is_numeric($priceMax) || $priceMax <= 0) {
            return;
        }

        $query
            ->withMin(
                ['services as min_price' => fn (Builder $q) => $q->where('is_active', true)],
                'price'
            )
            ->having('min_price', '<=', (int) $priceMax);
    }

    /**
     * سالن باید:
     *  1) امروز تو SalonDailyStatus بسته اعلام نشده باشه
     *  2) حداقل یه شیفت فعال داشته باشه که الان داخلش باشیم
     */
    private function applyOpenNow(Builder $query): void
    {
        $now        = Carbon::now();
        $dayOfWeek  = (int) $now->dayOfWeek; // 0=Sunday
        $today      = $now->toDateString();
        $time       = $now->format('H:i:s');

        $query
            ->whereDoesntHave('dailyStatuses', function (Builder $q) use ($today) {
                $q->where('date', $today)->where('is_closed', true);
            })
            ->whereHas('workingHours', function (Builder $q) use ($dayOfWeek, $time) {
                $q->where('day_of_week', $dayOfWeek)
                    ->where('is_closed', false)
                    ->whereTime('start_time', '<=', $time)
                    ->whereTime('end_time', '>=', $time);
            });
    }

    /**
     * «نوبت خالی امروز» — تخمین:
     *  امروز بسته نباشه + یه شیفت فعال برای امروز داشته باشه.
     *  (وقتی جدول time_slots اضافه شد، این رو دقیق می‌کنیم.)
     */
    private function applyHasSlotToday(Builder $query): void
    {
        $dayOfWeek = (int) Carbon::now()->dayOfWeek;
        $today     = Carbon::now()->toDateString();

        $query
            ->whereDoesntHave('dailyStatuses', function (Builder $q) use ($today) {
                $q->where('date', $today)->where('is_closed', true);
            })
            ->whereHas('workingHours', function (Builder $q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                    ->where('is_closed', false);
            });
    }

    private function applyGeo(Builder $query, array $filters): void
    {
        $lat    = (float) $filters['lat'];
        $lng    = (float) $filters['lng'];
        $radius = $filters['radius'] ?: self::DEFAULT_RADIUS_KM;

        $haversine = '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) )
                       * cos( radians( longitude ) - radians(?) )
                       + sin( radians(?) ) * sin( radians( latitude ) ) ) )';

        $query
            ->selectRaw("salons.*, {$haversine} AS distance_km", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance_km', '<=', $radius);
    }

    /* =====================================================================
     | Sorting
     * ===================================================================== */

    private function applySorting(Builder $query, string $sort, bool $hasGeo): void
    {
        switch ($sort) {
            case 'rating':
                $query->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('reviews_count');
                break;

            case 'price_asc':
                $query->orderBy('min_price');
                break;

            case 'price_desc':
                $query->orderByDesc('min_price');
                break;

            case 'distance':
                $hasGeo
                    ? $query->orderBy('distance_km')
                    : $query->latest('id');
                break;

            case 'newest':
                $query->latest('id');
                break;

            case 'recommended':
            default:
                $query->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('reviews_count')
                    ->latest('id');
                break;
        }
    }

    /* =====================================================================
     | Nearby
     * ===================================================================== */

    private function nearbySalons(array $filters, bool $hasGeo)
    {
        $query = $this->baseSalonQuery()
            ->with(['barbers' => function ($q) {
                $q->where('is_active', true)
                    ->select(['id', 'salon_id', 'name', 'specialty', 'image_path'])
                    ->orderBy('name')
                    ->limit(2);
            }]);

        if ($hasGeo) {
            $this->applyGeo($query, $filters);
            $query->orderBy('distance_km');
        } else {
            $query->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count');
        }

        return $query->limit(self::NEARBY_LIMIT)->get();
    }

    /* =====================================================================
     | Helper Data
     * ===================================================================== */

    private function serviceCategories(): array
    {
        return config('services.categories', [
            ['key' => 'haircut', 'label' => 'اصلاح و کوتاهی', 'icon' => 'scissors'],
            ['key' => 'color',   'label' => 'رنگ و مش',       'icon' => 'palette'],
            ['key' => 'keratin', 'label' => 'کراتین و احیا',  'icon' => 'sparkles'],
            ['key' => 'nails',   'label' => 'ناخن',           'icon' => 'hand'],
            ['key' => 'makeup',  'label' => 'میکاپ',          'icon' => 'brush'],
            ['key' => 'brows',   'label' => 'ابرو و مژه',     'icon' => 'eye'],
            ['key' => 'skin',    'label' => 'پوست',           'icon' => 'droplet'],
            ['key' => 'massage', 'label' => 'ماساژ',          'icon' => 'heart'],
            ['key' => 'bridal',  'label' => 'عروس',           'icon' => 'crown'],
        ]);
    }

    private function provinces()
    {
        return Salon::query()
            ->where('is_active', true)
            ->whereNotNull('province')
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');
    }

    private function cities(?string $province)
    {
        return Salon::query()
            ->where('is_active', true)
            ->whereNotNull('city')
            ->when($province, fn (Builder $q) => $q->where('province', $province))
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }
}
