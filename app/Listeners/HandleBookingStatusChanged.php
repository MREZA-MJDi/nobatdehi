<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Jobs\SendBookingSms;
use App\Models\Booking;
use App\Notifications\BookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleBookingStatusChanged implements ShouldQueue
{
    public int $tries = 5;

    public array $backoff = [5, 15, 60, 180];

    public function handle(BookingStatusChanged $event): void
    {
        $booking = Booking::query()
            ->with([
                'salon',
                'barber',
                'service',
                'customer',
            ])
            ->find($event->booking->id);

        if (!$booking || !$booking->customer) {
            return;
        }

        $booking->customer->notify(
            new BookingNotification(
                $booking,
                'status_changed'
            )
        );

        SendBookingSms::dispatch(
            $booking->id,
            'customer'
        );
    }
}
