<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query = trim(
            (string) $request->query('q', '')
        );

        $code = strtoupper(
            trim(
                (string) $request->query('code', '')
            )
        );

        $type = $request->query(
            'type',
            'all'
        );

        /*
        |--------------------------------------------------------------------------
        | Normalize Search Type
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $type,
                ['all', 'salon', 'barber'],
                true
            )
        ) {
            $type = 'all';
        }


        /*
        |--------------------------------------------------------------------------
        | Salon Query
        |--------------------------------------------------------------------------
        */

        $salonsQuery = Salon::query()
            ->where(
                'is_active',
                true
            )
            ->withCount([
                'barbers' => function ($builder) {
                    $builder->where(
                        'is_active',
                        true
                    );
                },

                'services' => function ($builder) {
                    $builder->where(
                        'is_active',
                        true
                    );
                },
            ]);


        /*
        |--------------------------------------------------------------------------
        | Salon Search
        |--------------------------------------------------------------------------
        */

        if ($type !== 'barber') {

            $salonsQuery->when(
                $query !== '',
                function ($builder) use ($query) {

                    $builder->where(function ($q) use ($query) {

                        $q->where(
                            'name',
                            'like',
                            '%' . $query . '%'
                        )

                            ->orWhere(
                                'description',
                                'like',
                                '%' . $query . '%'
                            )

                            ->orWhere(
                                'city',
                                'like',
                                '%' . $query . '%'
                            )

                            ->orWhere(
                                'district',
                                'like',
                                '%' . $query . '%'
                            )

                            ->orWhere(
                                'address',
                                'like',
                                '%' . $query . '%'
                            )

                            ->orWhere(
                                'code',
                                'like',
                                '%' . $query . '%'
                            );
                    });
                }
            );


            $salonsQuery->when(
                $code !== '',
                function ($builder) use ($code) {

                    $builder->where(
                        'code',
                        $code
                    );
                }
            );
        } else {

            /*
             * When searching only barbers, don't return all salons.
             */
            $salonsQuery->whereRaw(
                '1 = 0'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Salon Results
        |--------------------------------------------------------------------------
        */

        $salons = $salonsQuery
            ->latest('id')
            ->paginate(12)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Barber Search
        |--------------------------------------------------------------------------
        */

        $barbers = collect();


        if (
            $type === 'all' ||
            $type === 'barber'
        ) {

            $barbers = Barber::query()
                ->where(
                    'is_active',
                    true
                )

                ->whereHas(
                    'salon',
                    function ($builder) {
                        $builder->where(
                            'is_active',
                            true
                        );
                    }
                )

                ->with([
                    'salon',
                ])

                ->when(
                    $query !== '',
                    function ($builder) use ($query) {

                        $builder->where(function ($q) use ($query) {

                            $q->where(
                                'name',
                                'like',
                                '%' . $query . '%'
                            )

                                ->orWhere(
                                    'specialty',
                                    'like',
                                    '%' . $query . '%'
                                )

                                ->orWhere(
                                    'bio',
                                    'like',
                                    '%' . $query . '%'
                                );
                        });
                    }
                )

                ->latest('id')
                ->limit(12)
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Dynamic Statistics
        |--------------------------------------------------------------------------
        */

        $activeSalonCount = Salon::query()
            ->where(
                'is_active',
                true
            )
            ->count();


        $activeBarberCount = Barber::query()
            ->where(
                'is_active',
                true
            )
            ->whereHas(
                'salon',
                function ($builder) {
                    $builder->where(
                        'is_active',
                        true
                    );
                }
            )
            ->count();


        $bookingCount = Booking::query()
            ->whereHas(
                'salon',
                function ($builder) {
                    $builder->where(
                        'is_active',
                        true
                    );
                }
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Map Salons
        |--------------------------------------------------------------------------
        |
        | Only salons that actually have coordinates are sent
        | to the public map.
        |
        */

        $mapSalons = Salon::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get([
                'id',
                'name',
                'slug',
                'latitude',
                'longitude',
                'city',
                'district',
            ]);
        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.discover',
            [
                'salons' =>
                    $salons,

                'barbers' =>
                    $barbers,

                'mapSalons' =>
                    $mapSalons,

                'activeSalonCount' =>
                    $activeSalonCount,

                'activeBarberCount' =>
                    $activeBarberCount,

                'bookingCount' =>
                    $bookingCount,

                'query' =>
                    $query,

                'code' =>
                    $code,

                'type' =>
                    $type,
            ]
        );
    }
}

