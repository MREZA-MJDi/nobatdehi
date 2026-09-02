<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $event
    ) {
    }

    public function via(
        object $notifiable
    ): array {
        return [
            'database',
        ];
    }

    public function toArray(
        object $notifiable
    ): array {
        $booking = $this->booking;

        if (
            $this->event === 'created'
        ) {
            return [
                'type' =>
                    'booking_created',

                'title' =>
                    'نوبت جدید',

                'message' =>
                    'یک نوبت جدید برای ' .
                    ($booking->service?->name ?? 'خدمت') .
                    ' ثبت شده است.',

                'booking_id' =>
                    $booking->id,

                'salon_id' =>
                    $booking->salon_id,
            ];
        }

        return [
            'type' =>
                'booking_status_changed',

            'title' =>
                'وضعیت نوبت تغییر کرد',

            'message' =>
                'وضعیت نوبت شما به «' .
                $booking->status->label() .
                '» تغییر کرد.',

            'booking_id' =>
                $booking->id,

            'status' =>
                $booking->status->value,

            'salon_id' =>
                $booking->salon_id,
        ];
    }
}
