<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(
        Request $request,
        Booking $booking
    ): View|RedirectResponse {
        $booking = $request
            ->user()
            ->bookings()
            ->with([
                'salon',
                'barber',
                'service',
                'review',
            ])
            ->findOrFail(
                $booking->id
            );


        abort_unless(
            $booking->status === BookingStatus::COMPLETED,
            404
        );


        return view(
            'customer.reviews.create',
            compact(
                'booking'
            )
        );
    }


    public function store(
        StoreReviewRequest $request,
        Booking $booking
    ): RedirectResponse {
        $booking = $request
            ->user()
            ->bookings()
            ->with([
                'salon',
            ])
            ->findOrFail(
                $booking->id
            );


        if (
            $booking->status !==
            BookingStatus::COMPLETED
        ) {
            return back()
                ->withErrors([
                    'review' =>
                        'فقط برای نوبت تکمیل‌شده می‌توانید نظر ثبت کنید.',
                ]);
        }


        $data = $request->validated();


        $booking->review()->updateOrCreate(
            [
                'booking_id' =>
                    $booking->id,
            ],
            [
                'salon_id' =>
                    $booking->salon_id,

                'customer_id' =>
                    $request->user()->id,

                'rating' =>
                    $data['rating'],

                'comment' =>
                    $data['comment'] ?? null,

                'is_published' =>
                    true,
            ]
        );


        return redirect()
            ->route(
                'customer.dashboard'
            )
            ->with(
                'success',
                'نظر شما با موفقیت ثبت شد.'
            );
    }
}
