<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Jobs\SendBookingSms;
use App\Models\Booking;
use App\Notifications\BookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class HandleBookingStatusChanged implements ShouldQueue, ShouldBeUnique
{
    public int $tries = 5;

    public int $uniqueFor = 300;

    public array $backoff = [5, 15, 60, 180];

    public function uniqueId(BookingStatusChanged $event): string
    {
        return 'booking-status:' . $event->booking->id . ':' . $event->to->value . ':' . $event->from->value;
    }

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
