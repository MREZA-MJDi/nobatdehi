<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->withCount([
                'barbers',
                'services',
                'bookings',
            ])
            ->with([
                'workingHours',
            ])
            ->firstOrFail();

        $unreadNotifications = $request
            ->user()
            ->unreadNotifications()
            ->count();

        $pendingBookings = $salon
            ->bookings()
            ->where(
                'status',
                'pending'
            )
            ->count();

        $todayBookings = $salon
            ->bookings()
            ->whereDate(
                'booking_date',
                today()
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'confirmed',
                ]
            )
            ->count();

        return view(
            'salon.dashboard',
            compact(
                'salon',
                'unreadNotifications',
                'pendingBookings',
                'todayBookings'
            )
        );
    }
}
