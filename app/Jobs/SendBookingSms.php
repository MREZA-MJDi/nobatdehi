<?php

namespace App\Jobs;

use App\Contracts\SmsSender;
use App\Models\Booking;
use App\Support\PhoneNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 90, 180];

    public function __construct(
        public int $bookingId,
        public string $recipientType = 'approver'
    ) {
        $this->onQueue('notifications');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'booking-sms:' . $this->bookingId . ':' . $this->recipientType
            ))->expireAfter(300)->releaseAfter(10),
        ];
    }

    public function handle(SmsSender $smsSender): void
    {
        $booking = Booking::query()
            ->with([
                'salon.owner',
                'barber',
                'service',
                'customer',
            ])
            ->find($this->bookingId);

        if (!$booking) {
            return;
        }

        $recipient = $this->recipient($booking);

        if (!$recipient) {
            Log::warning('NOBAT booking SMS skipped: no recipient phone', [
                'booking_id' => $booking->id,
                'recipient_type' => $this->recipientType,
            ]);

            return;
        }

        try {
            $phone = PhoneNumber::normalize($recipient['phone']);
        } catch (\Throwable $exception) {
            Log::warning('NOBAT booking SMS skipped: invalid phone', [
                'booking_id' => $booking->id,
                'recipient_type' => $this->recipientType,
                'phone' => $recipient['phone'],
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        if ($this->recipientType === 'approver' && $booking->status->value !== 'pending') {
            return;
        }

        $message = $this->message($booking, $recipient['name']);

        $smsSender->send($phone, $message);
    }

    private function recipient(Booking $booking): ?array
    {
        if ($this->recipientType === 'customer') {
            return $booking->customer && filled($booking->customer->phone)
                ? [
                    'name' => $booking->customer->name,
                    'phone' => $booking->customer->phone,
                ]
                : null;
        }

        if ($booking->barber && filled($booking->barber->phone)) {
            return [
                'name' => $booking->barber->name,
                'phone' => $booking->barber->phone,
            ];
        }

        if ($booking->salon?->owner && filled($booking->salon->owner->phone)) {
            return [
                'name' => $booking->salon->owner->name,
                'phone' => $booking->salon->owner->phone,
            ];
        }

        return null;
    }

    private function message(
        Booking $booking,
        string $recipientName
    ): string {
        $date = $booking->booking_date?->format('Y/m/d')
            ?? (string) $booking->booking_date;

        $start = substr((string) $booking->start_time, 0, 5);

        if ($this->recipientType === 'customer') {
            $message = match ($booking->status->value) {
                'confirmed' => [
                    'نوبت NOBAT',
                    'سلام ' . ($booking->customer?->name ?? ''),
                    'نوبت شما در «' . ($booking->salon?->name ?? 'سالن') . '» تأیید شد.',
                    'خدمت: ' . ($booking->service?->name ?? 'خدمت'),
                    'تاریخ: ' . $date,
                    'ساعت: ' . $start,
                ],

                'cancelled' => [
                    'نوبت NOBAT',
                    'سلام ' . ($booking->customer?->name ?? ''),
                    'نوبت شما در «' . ($booking->salon?->name ?? 'سالن') . '» لغو شد.',
                    'خدمت: ' . ($booking->service?->name ?? 'خدمت'),
                    'تاریخ: ' . $date,
                    'ساعت: ' . $start,
                ],

                'completed' => [
                    'نوبت NOBAT',
                    'نوبت شما در «' . ($booking->salon?->name ?? 'سالن') . '» تکمیل شد.',
                ],

                default => [],
            };

            return implode("\n", $message);
        }

        return implode("\n", [
            'NOBAT / نوبت جدید',
            'سالن: ' . ($booking->salon?->name ?? 'سالن'),
            'مشتری: ' . ($booking->customer?->name ?? 'مشتری'),
            'خدمت: ' . ($booking->service?->name ?? 'خدمت'),
            'تاریخ: ' . $date,
            'ساعت: ' . $start,
            'کد نوبت: ' . $booking->id,
            'برای تأیید: 1',
            'برای لغو: 2',
        ]);
    }
}
