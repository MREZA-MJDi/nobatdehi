<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user();

        $bookings = $customer
            ->bookings()
            ->with([
                'salon',
                'barber',
                'service',
                'review',
            ])
            ->orderByRaw(
                <<<'SQL'
                CASE
                    WHEN booking_date >= CURRENT_DATE THEN 0
                    ELSE 1
                END ASC
                SQL
            )
            ->orderByRaw(
                <<<'SQL'
                CASE
                    WHEN booking_date >= CURRENT_DATE
                    THEN booking_date
                    ELSE NULL
                END ASC
                SQL
            )
            ->orderByRaw(
                <<<'SQL'
                CASE
                    WHEN booking_date >= CURRENT_DATE
                    THEN start_time
                    ELSE NULL
                END ASC
                SQL
            )
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->paginate(8)
            ->withQueryString();

        return view(
            'customer.account.dashboard',
            compact('bookings')
        );
    }


    public function bookings(Request $request): View
    {
        return $this->index($request);
    }
}
