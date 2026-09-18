<?php

namespace App\Events;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public BookingStatus $from,
        public BookingStatus $to
    ) {
    }
}
