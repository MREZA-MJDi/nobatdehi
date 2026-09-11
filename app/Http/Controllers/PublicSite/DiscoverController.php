<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        /*
        |--------------------------------------------------------------------------
        | Base Salon Query
        |--------------------------------------------------------------------------
        |
        | فقط داده‌هایی که Discover واقعاً برای کارت‌ها نیاز دارد.
        |
        */

        $salonQuery = Salon::query()
            ->where('is_active', true)

            ->withCount([
                'services' => fn (Builder $query) =>
                $query->where('is_active', true),

                'barbers' => fn (Builder $query) =>
                $query->where('is_active', true),
            ])

            ->withAvg('reviews', 'rating')

            ->with([
                'services' => function (Builder $query) {
                    $query
                        ->where('is_active', true)
                        ->select([
                            'id',
                            'salon_id',
                            'name',
                            'price',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->limit(3);
                },
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $salonQuery->where(function (Builder $query) use ($search) {

                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");

                $query->orWhereHas('services', function (Builder $serviceQuery) use ($search) {

                    $serviceQuery
                        ->where('is_active', true)
                        ->where(function (Builder $query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        });
                });

                $query->orWhereHas('barbers', function (Builder $barberQuery) use ($search) {

                    $barberQuery
                        ->where('is_active', true)
                        ->where(function (Builder $query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere(
                                    'specialty',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'bio',
                                    'like',
                                    "%{$search}%"
                                );
                        });
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Main Salons
        |--------------------------------------------------------------------------
        */

        $salons = $salonQuery
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Featured Salon
        |--------------------------------------------------------------------------
        |
        | فعلاً یک معیار ساده و قابل پیش‌بینی:
        | سالن فعال + دارای مختصات + بالاترین rating
        |
        */

        $featuredSalon = Salon::query()
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with([
                'services' => function (Builder $query) {
                    $query
                        ->where('is_active', true)
                        ->select([
                            'id',
                            'salon_id',
                            'name',
                            'price',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->limit(3);
                },
            ])
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Nearby / Discoverable Salons
        |--------------------------------------------------------------------------
        |
        | هنوز فاصله واقعی نداریم.
        | پس اسم "nearby" را به معنای location-ready نگه می‌داریم
        | تا زمانی که مختصات کاربر وارد flow شود.
        |
        */

        $nearbySalons = Salon::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')

            ->withAvg('reviews', 'rating')

            ->withCount([
                'reviews',
                'services' => fn (Builder $query) =>
                $query->where('is_active', true),
            ])

            ->with([
                'services' => function (Builder $query) {
                    $query
                        ->where('is_active', true)
                        ->select([
                            'id',
                            'salon_id',
                            'name',
                            'price',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->limit(3);
                },

                'barbers' => function (Builder $query) {
                    $query
                        ->where('is_active', true)
                        ->select([
                            'id',
                            'salon_id',
                            'name',
                            'specialty',
                            'image_path',
                        ])
                        ->orderBy('name')
                        ->limit(2);
                },
            ])

            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->latest('id')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        $popularServices = Service::query()
            ->where('is_active', true)

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

            ->latest('id')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Stylists
        |--------------------------------------------------------------------------
        |
        | مستقیماً از barber query می‌گیریم،
        | نه اینکه از nearbySalons دوباره استخراج کنیم.
        |
        */

        $stylists = Barber::query()
            ->where('is_active', true)

            ->with([
                'salon:id,name,slug,code',
            ])

            ->select([
                'id',
                'salon_id',
                'name',
                'specialty',
                'bio',
                'image_path',
            ])

            ->latest('id')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'salons' => Salon::query()
                ->where('is_active', true)
                ->count(),

            'barbers' => Barber::query()
                ->where('is_active', true)
                ->count(),

            'services' => Service::query()
                ->where('is_active', true)
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view('customer.discover', [
            'salons' => $salons,
            'nearbySalons' => $nearbySalons,
            'featuredSalon' => $featuredSalon,
            'popularServices' => $popularServices,
            'stylists' => $stylists,
            'stats' => $stats,
            'search' => $search,
        ]);
    }
}
