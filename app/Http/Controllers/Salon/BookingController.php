<?php

namespace App\Http\Controllers\Salon;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\BookingStatusRequest;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(
        Request $request
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        $bookings = $salon
            ->bookings()
            ->with([
                'barber',
                'service',
                'customer',
            ])
            ->latest('booking_date')
            ->latest('start_time')
            ->paginate(20);

        return view(
            'salon.bookings.index',
            compact(
                'salon',
                'bookings'
            )
        );
    }


    public function show(
        Request $request,
        Booking $booking
    ): View {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Ownership Protection
        |--------------------------------------------------------------------------
        */

        $booking = $salon
            ->bookings()
            ->with([
                'barber',
                'service',
                'customer',
            ])
            ->findOrFail(
                $booking->id
            );

        return view(
            'salon.bookings.show',
            compact(
                'salon',
                'booking'
            )
        );
    }


    public function updateStatus(
        BookingStatusRequest $request,
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Ownership Protection
        |--------------------------------------------------------------------------
        */

        $booking = $salon
            ->bookings()
            ->findOrFail(
                $booking->id
            );

        $status = BookingStatus::from(
            $request->validated('status')
        );

        $bookingService->changeStatus(
            $booking,
            $status
        );

        return back()
            ->with(
                'success',
                'وضعیت نوبت با موفقیت تغییر کرد و مشتری مطلع شد.'
            );
    }
}
