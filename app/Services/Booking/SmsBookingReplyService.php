<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\SmsInboundMessage;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SmsBookingReplyService
{
    public function handle(
        string $fromPhone,
        string $body,
        ?string $providerMessageId = null,
        array $payload = []
    ): array {
        $phone = PhoneNumber::normalize($fromPhone);
        $body = $this->normalizeDigits(trim($body));

        if ($body === '') {
            return $this->recordRejected(
                $phone,
                $body,
                $providerMessageId,
                'empty_message',
                $payload
            );
        }

        [$action, $reference] = $this->parseCommand($body);

        if ($action === null) {
            return $this->recordRejected(
                $phone,
                $body,
                $providerMessageId,
                'unsupported_command',
                $payload
            );
        }

        return DB::transaction(function () use (
            $phone,
            $body,
            $providerMessageId,
            $payload,
            $action,
            $reference
        ) {
            if ($providerMessageId) {
                $existing = SmsInboundMessage::query()
                    ->where('provider_message_id', $providerMessageId)
                    ->first();

                if ($existing) {
                    return [
                        'ok' => true,
                        'duplicate' => true,
                        'status' => $existing->status,
                        'booking_id' => $existing->booking_id,
                    ];
                }
            }

            $message = SmsInboundMessage::create([
                'provider_message_id' => $providerMessageId,
                'from_phone' => $phone,
                'body' => $body,
                'action' => $action === 'confirm'
                    ? 'confirm'
                    : 'cancel',
                'status' => 'received',
                'payload' => $payload ?: null,
            ]);

            $booking = $this->resolveBooking(
                $phone,
                $reference
            );

            if (!$booking) {
                $message->update([
                    'status' => 'rejected',
                    'error' => $reference === null
                        ? 'booking_not_found_or_ambiguous'
                        : 'booking_not_found',
                    'processed_at' => now(),
                ]);

                return [
                    'ok' => false,
                    'status' => 'rejected',
                    'reason' => $reference === null
                        ? 'booking_not_found_or_ambiguous'
                        : 'booking_not_found',
                ];
            }

            $booking->lockForUpdate();

            if ($booking->status !== BookingStatus::PENDING) {
                $message->update([
                    'booking_id' => $booking->id,
                    'status' => 'ignored',
                    'error' => 'booking_already_resolved',
                    'processed_at' => now(),
                ]);

                return [
                    'ok' => true,
                    'status' => 'ignored',
                    'booking_id' => $booking->id,
                ];
            }

            $bookingStatus = $action === 'confirm'
                ? BookingStatus::CONFIRMED
                : BookingStatus::CANCELLED;

            $bookingService = app(BookingService::class);

            $bookingService->changeStatus(
                $booking,
                $bookingStatus
            );

            $message->update([
                'booking_id' => $booking->id,
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return [
                'ok' => true,
                'status' => 'processed',
                'booking_id' => $booking->id,
                'booking_status' => $bookingStatus->value,
            ];
        });
    }

    private function resolveBooking(
        string $phone,
        ?int $reference
    ): ?Booking {
        $variants = $this->phoneVariants($phone);

        $query = Booking::query()
            ->with([
                'salon.owner',
                'barber',
                'service',
                'customer',
            ])
            ->where(
                'status',
                BookingStatus::PENDING
            )
            ->whereDate(
                'booking_date',
                '>=',
                now(config('app.timezone', 'Asia/Tehran'))->toDateString()
            )
            ->where(function ($query) use ($variants) {
                $query
                    ->whereHas(
                        'barber',
                        fn ($barberQuery) =>
                        $barberQuery->whereIn('phone', $variants)
                    )
                    ->orWhereHas(
                        'salon.owner',
                        fn ($ownerQuery) =>
                        $ownerQuery->whereIn('phone', $variants)
                    );
            })
            ->latest('id');

        if ($reference !== null) {
            return $query
                ->whereKey($reference)
                ->first();
        }

        $matches = $query
            ->limit(2)
            ->get();

        return $matches->count() === 1
            ? $matches->first()
            : null;
    }

    private function phoneVariants(
        string $phone
    ): array {
        $phone = PhoneNumber::normalize($phone);

        return array_values(array_unique([
            $phone,
            '+98' . substr($phone, 1),
            '98' . substr($phone, 1),
        ]));
    }

    private function parseCommand(
        string $body
    ): array {
        if (preg_match('/^([12])$/', $body, $matches)) {
            return [
                $matches[1] === '1' ? 'confirm' : 'cancel',
                null,
            ];
        }

        if (preg_match('/^([12])[-_: ]([0-9]{1,12})$/', $body, $matches)) {
            return [
                $matches[1] === '1' ? 'confirm' : 'cancel',
                (int) $matches[2],
            ];
        }

        return [null, null];
    }

    private function normalizeDigits(
        string $value
    ): string {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }

    private function recordRejected(
        string $phone,
        string $body,
        ?string $providerMessageId,
        string $error,
        array $payload
    ): array {
        if ($providerMessageId && SmsInboundMessage::query()
            ->where('provider_message_id', $providerMessageId)
            ->exists()) {
            return [
                'ok' => true,
                'duplicate' => true,
                'status' => 'ignored',
            ];
        }

        SmsInboundMessage::create([
            'provider_message_id' => $providerMessageId,
            'from_phone' => $phone,
            'body' => $body,
            'status' => 'rejected',
            'error' => $error,
            'payload' => $payload ?: null,
            'processed_at' => now(),
        ]);

        return [
            'ok' => false,
            'status' => 'rejected',
            'reason' => $error,
        ];
    }
}
