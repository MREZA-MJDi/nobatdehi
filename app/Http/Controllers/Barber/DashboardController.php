<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Barber Dashboard
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view(
            'barber.dashboard',
            [
                'user' => $user,
            ]
        );
    }
}
