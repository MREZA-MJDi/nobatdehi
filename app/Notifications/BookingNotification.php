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
        $date = optional($booking->booking_date)->toDateString();
        $startTime = substr((string) $booking->start_time, 0, 5);
        $endTime = substr((string) $booking->end_time, 0, 5);
        $customerName = $booking->customer?->name
            ?? $booking->customer_name
            ?? 'مشتری';
        $serviceName = $booking->service?->name ?? 'خدمت';
        $barberName = $booking->barber?->name ?? 'متخصص';
        $salonName = $booking->salon?->name ?? 'سالن';

        if ($this->event === 'created') {
            return [
                'type' => 'booking_created',
                'title' => 'نوبت جدید — نیاز به رسیدگی',
                'message' => $customerName . ' برای «' . $serviceName .
                    '» با ' . $barberName . ' در ساعت ' . $startTime .
                    ' نوبت گرفته است.',
                'booking_id' => $booking->id,
                'status' => $booking->status->value,
                'salon_id' => $booking->salon_id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'customer_name' => $customerName,
                'service_name' => $serviceName,
                'barber_name' => $barberName,
            ];
        }

        if ($this->event === 'customer_cancelled') {
            return [
                'type' => 'booking_cancelled_by_customer',
                'title' => 'لغو نوبت توسط مشتری',
                'message' => $customerName . ' نوبت «' . $serviceName .
                    '» در ساعت ' . $startTime . ' را لغو کرد.',
                'booking_id' => $booking->id,
                'status' => $booking->status->value,
                'salon_id' => $booking->salon_id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'customer_name' => $customerName,
                'service_name' => $serviceName,
                'barber_name' => $barberName,
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
                    'نوبت شما در «' . $salonName .
                    '» برای ساعت ' . $startTime . ' تأیید شد.',
                BookingStatus::CANCELLED =>
                    'نوبت شما در «' . $salonName . '» لغو شد.',
                BookingStatus::COMPLETED =>
                    'نوبت شما در «' . $salonName . '» تکمیل شد.',
                default =>
                    'وضعیت نوبت شما به «' .
                    $booking->status->label() .
                    '» تغییر کرد.',
            },
            'booking_id' => $booking->id,
            'status' => $booking->status->value,
            'salon_id' => $booking->salon_id,
            'previous_status' => $this->previousStatus?->value,
            'booking_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'customer_name' => $customerName,
            'service_name' => $serviceName,
            'barber_name' => $barberName,
        ];
    }
}
