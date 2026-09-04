<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ): View {

        $bookings = $request
            ->user()
            ->bookings()
            ->with([
                'salon',
                'barber',
                'service',
                'review',
            ])
            ->latest('booking_date')
            ->latest('start_time')
            ->paginate(8)
            ->withQueryString();


        $upcomingCount = $request
            ->user()
            ->bookings()
            ->whereIn(
                'status',
                [
                    BookingStatus::PENDING->value,
                    BookingStatus::CONFIRMED->value,
                ]
            )
            ->whereDate(
                'booking_date',
                '>=',
                today()
            )
            ->count();


        $completedCount = $request
            ->user()
            ->bookings()
            ->where(
                'status',
                BookingStatus::COMPLETED->value
            )
            ->count();


        $cancelledCount = $request
            ->user()
            ->bookings()
            ->where(
                'status',
                BookingStatus::CANCELLED->value
            )
            ->count();


        return view(
            'customer.account.dashboard',
            compact(
                'bookings',
                'upcomingCount',
                'completedCount',
                'cancelledCount'
            )
        );
    }


    public function bookings(
        Request $request
    ): View {

        return $this->index($request);
    }
}
