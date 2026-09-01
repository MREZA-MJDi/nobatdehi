<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                'salonCount' =>
                    Salon::count(),

                'activeSalonCount' =>
                    Salon::active()->count(),

                'barberCount' =>
                    User::query()
                        ->where(
                            'role',
                            'barber'
                        )
                        ->count(),

                'customerCount' =>
                    User::query()
                        ->where(
                            'role',
                            'customer'
                        )
                        ->count(),
            ]
        );
    }
}
