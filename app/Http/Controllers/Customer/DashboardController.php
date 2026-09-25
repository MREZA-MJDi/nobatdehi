<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user();

        $baseBookings = fn () => $customer
            ->bookings()
            ->with([
                'salon',
                'barber',
                'service',
                'review',
            ]);

        $upcoming = $baseBookings()
            ->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED,
            ])
            ->where(function ($query) {
                $query->whereDate('booking_date', '>', today())
                    ->orWhere(function ($query) {
                        $query
                            ->whereDate('booking_date', today())
                            ->where('start_time', '>=', now()->format('H:i:s'));
                    });
            })
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->first();

        $recentBookings = $baseBookings()
            ->latest('booking_date')
            ->latest('start_time')
            ->limit(4)
            ->get();

        $stats = [
            'total' => $customer->bookings()->count(),
            'pending' => $customer->bookings()
                ->where('status', BookingStatus::PENDING)
                ->count(),
            'completed' => $customer->bookings()
                ->where('status', BookingStatus::COMPLETED)
                ->count(),
            'favorites' => $customer->favoriteSalons()->count(),
            'unread' => $customer->unreadNotifications()->count(),
        ];

        $favoriteSalons = $customer
            ->favoriteSalons()
            ->where('is_active', true)
            ->withCount([
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->withAvg([
                'reviews' => fn ($query) => $query->where('is_published', true),
            ], 'rating')
            ->latest('salon_favorites.created_at')
            ->limit(3)
            ->get();

        $upcomingActions = [
            'can_edit' => $upcoming?->customerCanEdit() ?? false,
            'can_cancel' => $upcoming?->customerCanCancel() ?? false,
        ];

        return view(
            'customer.account.dashboard',
            [
                'upcoming' => $upcoming,
                'upcomingActions' => $upcomingActions,
                'recentBookings' => $recentBookings,
                'favoriteSalons' => $favoriteSalons,
                'stats' => $stats,
            ]
        );
    }

    public function bookings(Request $request): View
    {
        $customer = $request->user();

        $status = $request->query('status');

        $query = $customer
            ->bookings()
            ->with([
                'salon',
                'barber',
                'service',
                'review',
            ]);

        if (in_array($status, [
            BookingStatus::PENDING->value,
            BookingStatus::CONFIRMED->value,
            BookingStatus::COMPLETED->value,
            BookingStatus::CANCELLED->value,
        ], true)) {
            $query->where('status', $status);
        } else {
            $status = 'all';
        }

        $bookings = $query
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
            ->paginate(10)
            ->withQueryString();

        $bookings->getCollection()->each(
            function ($booking): void {
                $booking->setAttribute(
                    '_customer_can_edit',
                    $booking->customerCanEdit()
                );

                $booking->setAttribute(
                    '_customer_can_cancel',
                    $booking->customerCanCancel()
                );

                $booking->setAttribute(
                    '_customer_can_review',
                    $booking->customerCanReview()
                );

                $booking->setAttribute(
                    '_customer_has_review',
                    $booking->customerHasReview()
                );
            }
        );

        return view(
            'customer.bookings.index',
            compact('bookings', 'status')
        );
    }
}
