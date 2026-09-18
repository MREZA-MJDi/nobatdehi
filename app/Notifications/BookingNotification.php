<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $event,
        public ?BookingStatus $previousStatus = null
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

        if ($this->event === 'created') {
            $isCustomer =
                method_exists($notifiable, 'isCustomer') &&
                $notifiable->isCustomer();

            if (
                $isCustomer &&
                $booking->status === BookingStatus::CONFIRMED
            ) {
                return [
                    'type' => 'booking_created',
                    'title' => 'نوبت تأیید شد',
                    'message' =>
                        'نوبت شما برای «' .
                        ($booking->service?->name ?? 'خدمت') .
                        '» در «' .
                        ($booking->salon?->name ?? 'سالن') .
                        '» با موفقیت ثبت و تأیید شد.',
                    'booking_id' => $booking->id,
                    'status' => $booking->status->value,
                    'salon_id' => $booking->salon_id,
                ];
            }

            if ($isCustomer) {
                return [
                    'type' => 'booking_created',
                    'title' => 'نوبت ثبت شد',
                    'message' =>
                        'نوبت شما برای «' .
                        ($booking->service?->name ?? 'خدمت') .
                        '» در «' .
                        ($booking->salon?->name ?? 'سالن') .
                        '» ثبت شد و در انتظار تأیید متخصص است.',
                    'booking_id' => $booking->id,
                    'status' => $booking->status->value,
                    'salon_id' => $booking->salon_id,
                ];
            }

            return [
                'type' => 'booking_created',
                'title' => 'نوبت جدید — نیاز به رسیدگی',
                'message' =>
                    'یک نوبت جدید برای «' .
                    ($booking->service?->name ?? 'خدمت') .
                    '» ثبت شده است. کد نوبت: ' .
                    $booking->id .
                    '. برای تأیید یا لغو از پنل یا پیامک اقدام کنید.',
                'booking_id' => $booking->id,
                'status' => $booking->status->value,
                'salon_id' => $booking->salon_id,
            ];
        }

        return [
            'type' => 'booking_status_changed',
            'title' => match ($booking->status) {
                BookingStatus::CONFIRMED => 'نوبت تأیید شد',
                BookingStatus::CANCELLED => 'نوبت لغو شد',
                BookingStatus::COMPLETED => 'نوبت تکمیل شد',
                default => 'وضعیت نوبت تغییر کرد',
            },
            'message' => match ($booking->status) {
                BookingStatus::CONFIRMED =>
                    'نوبت شما در «' .
                    ($booking->salon?->name ?? 'سالن') .
                    '» برای ساعت ' .
                    substr((string) $booking->start_time, 0, 5) .
                    ' تأیید شد.',
                BookingStatus::CANCELLED =>
                    'نوبت شما در «' .
                    ($booking->salon?->name ?? 'سالن') .
                    '» لغو شد.',
                BookingStatus::COMPLETED =>
                    'نوبت شما در «' .
                    ($booking->salon?->name ?? 'سالن') .
                    '» تکمیل شد.',
                default =>
                    'وضعیت نوبت شما به «' .
                    $booking->status->label() .
                    '» تغییر کرد.',
            },
            'booking_id' => $booking->id,
            'status' => $booking->status->value,
            'salon_id' => $booking->salon_id,
            'previous_status' => $this->previousStatus?->value,
        ];
    }
}
