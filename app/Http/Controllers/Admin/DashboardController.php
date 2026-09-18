<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view(
            'admin.dashboard',
            [
                /*
                |--------------------------------------------------------------------------
                | Salons
                |--------------------------------------------------------------------------
                */

                'salonCount' => Salon::query()
                    ->count(),

                'activeSalonCount' => Salon::query()
                    ->where('is_active', true)
                    ->count(),


                /*
                |--------------------------------------------------------------------------
                | Barbers
                |--------------------------------------------------------------------------
                |
                | Barber is a separate model, not a User account.
                |
                */

                'barberCount' => Barber::query()
                    ->count(),


                /*
                |--------------------------------------------------------------------------
                | Customers
                |--------------------------------------------------------------------------
                */

                'customerCount' => User::query()
                    ->where(
                        'role',
                        UserRole::CUSTOMER->value
                    )
                    ->count(),
            ]
        );
    }
}
