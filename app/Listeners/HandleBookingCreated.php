<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendBookingSms;
use App\Models\Booking;
use App\Notifications\BookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class HandleBookingCreated implements ShouldQueue, ShouldBeUnique
{
    public int $tries = 5;

    public int $uniqueFor = 300;

    public array $backoff = [5, 15, 60, 180];

    public function uniqueId(BookingCreated $event): string
    {
        return 'booking-created:' . $event->booking->id;
    }

    public function handle(BookingCreated $event): void
    {
        $booking = Booking::query()
            ->with([
                'salon.owner',
                'barber',
                'service',
                'customer',
            ])
            ->find($event->booking->id);

        if (!$booking) {
            return;
        }

        if ($booking->customer) {
            $booking->customer->notify(
                new BookingNotification($booking, 'created')
            );
        }

        if (
            $booking->status->blocksAvailability() &&
            $booking->status->value === 'pending' &&
            $booking->salon?->owner
        ) {
            $booking->salon->owner->notify(
                new BookingNotification($booking, 'created')
            );

            SendBookingSms::dispatch(
                $booking->id,
                'approver'
            );
        }
    }
}
