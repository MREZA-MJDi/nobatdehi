<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim(
            (string) $request->query('q', '')
        );

        $code = trim(
            (string) $request->query('code', '')
        );

        $type = $request->query('type');


        /*
        |--------------------------------------------------------------------------
        | Salons
        |--------------------------------------------------------------------------
        */

        $salons = Salon::query()
            ->where('is_active', true)

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
                            );
                    });
                }
            )

            ->when(
                $code !== '',
                function ($builder) use ($code) {
                    $builder->where(
                        'code',
                        $code
                    );
                }
            )

            ->withCount([
                'barbers' => function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                },
            ])

            ->latest()

            ->paginate(12)

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $activeSalonCount = Salon::query()
            ->where(
                'is_active',
                true
            )
            ->count();

        $barberCount = Barber::query()
            ->where(
                'is_active',
                true
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.discover',
            [
                'salons' => $salons,

                'activeSalonCount' =>
                    $activeSalonCount,

                'barberCount' =>
                    $barberCount,

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
