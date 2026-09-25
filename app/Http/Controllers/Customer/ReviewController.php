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
            ->findOrFail($booking->id);

        abort_unless(
            $booking->status === BookingStatus::COMPLETED,
            404
        );

        if ($booking->review) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'status',
                    'برای این نوبت قبلاً نظر ثبت کرده‌اید.'
                );
        }

        return view(
            'customer.reviews.create',
            compact('booking')
        );
    }


    public function createSalon(
        Request $request,
        Salon $salon
    ): View|RedirectResponse {
        abort_unless($salon->is_active, 404);

        $existing = $salon
            ->reviews()
            ->where('customer_id', $request->user()->id)
            ->whereNull('booking_id')
            ->first();

        if ($existing) {
            return redirect()
                ->route('public.salons.show', $salon)
                ->with(
                    'status',
                    'شما قبلاً برای این سالن امتیاز ثبت کرده‌اید.'
                );
        }

        return view(
            'customer.reviews.salon',
            compact('salon')
        );
    }


    public function storeSalon(
        StoreReviewRequest $request,
        Salon $salon
    ): RedirectResponse {
        abort_unless($salon->is_active, 404);

        $existing = $salon
            ->reviews()
            ->where('customer_id', $request->user()->id)
            ->whereNull('booking_id')
            ->exists();

        if ($existing) {
            return redirect()
                ->route('public.salons.show', $salon)
                ->with(
                    'status',
                    'شما قبلاً برای این سالن امتیاز ثبت کرده‌اید.'
                );
        }

        $data = $request->validated();

        $salon->reviews()->create([
            'salon_id' => $salon->id,
            'customer_id' => $request->user()->id,
            'booking_id' => null,
            'rating' => $data['rating'],
            'comment' => filled($data['comment'] ?? null)
                ? trim($data['comment'])
                : null,
            'is_published' => true,
        ]);

        return redirect()
            ->route('public.salons.show', $salon)
            ->with(
                'success',
                'امتیاز شما با موفقیت ثبت شد.'
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
                'review',
            ])
            ->findOrFail($booking->id);

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

        if ($booking->review) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'status',
                    'برای این نوبت قبلاً نظر ثبت کرده‌اید.'
                );
        }

        $data = $request->validated();

        $booking->review()->create([
            'salon_id' =>
                $booking->salon_id,

            'customer_id' =>
                $request->user()->id,

            'booking_id' =>
                $booking->id,

            'rating' =>
                $data['rating'],

            'comment' =>
                filled($data['comment'] ?? null)
                    ? trim($data['comment'])
                    : null,

            'is_published' =>
                true,
        ]);

        return redirect()
            ->route('customer.dashboard')
            ->with(
                'success',
                'نظر شما با موفقیت ثبت شد.'
            );
    }
}
