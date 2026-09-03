<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\View\View;

class SalonController extends Controller
{
    public function show(
        Salon $salon
    ): View {
        abort_unless(
            $salon->is_active,
            404
        );


        $salon->load([
            'barbers' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->latest();
            },

            'services' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },

            'portfolioItems' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->with([
                        'barber',
                        'service',
                    ])
                    ->orderBy('sort_order')
                    ->latest('id');
            },

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


        return view(
            'public.salon',
            [
                'salon' => $salon,
            ]
        );
    }
}
