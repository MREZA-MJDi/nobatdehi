<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AvailabilityService
{
    private const SLOT_INTERVAL_MINUTES = 15;

    public function slots(
        Salon $salon,
        Barber $barber,
        Service $service,
        CarbonInterface $date,
        ?int $ignoreBookingId = null
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Validate relations
        |--------------------------------------------------------------------------
        */

        if (
            (int) $barber->salon_id !== (int) $salon->id ||
            (int) $service->salon_id !== (int) $salon->id
        ) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Validate active entities
        |--------------------------------------------------------------------------
        */

        if (
            !$salon->is_active ||
            !$barber->is_active ||
            !$service->is_active
        ) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Persian week mapping
        |--------------------------------------------------------------------------
        |
        | Carbon:
        | Sunday    = 0
        | Monday    = 1
        | Tuesday   = 2
        | Wednesday = 3
        | Thursday  = 4
        | Friday    = 5
        | Saturday  = 6
        |
        | Application:
        | Saturday  = 0
        | Sunday    = 1
        | Monday    = 2
        | Tuesday   = 3
        | Wednesday = 4
        | Thursday  = 5
        | Friday    = 6
        |
        */

        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

        /*
        |--------------------------------------------------------------------------
        | Get working intervals
        |--------------------------------------------------------------------------
        */

        $workingHours = $salon
            ->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_closed', false)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        if ($workingHours->isEmpty()) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Service duration
        |--------------------------------------------------------------------------
        */

        $duration = max(
            1,
            (int) $service->duration_minutes
        );

        /*
        |--------------------------------------------------------------------------
        | Blocking booking statuses
        |--------------------------------------------------------------------------
        */

        $blockingStatuses = collect(
            BookingStatus::cases()
        )
            ->filter(
                fn (BookingStatus $status): bool =>
                $status->blocksAvailability()
            )
            ->map(
                fn (BookingStatus $status): string =>
                $status->value
            )
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Existing bookings
        |--------------------------------------------------------------------------
        */

        $blockedBookings = $barber
            ->bookings()
            ->whereDate(
                'booking_date',
                $date->toDateString()
            )
            ->whereIn(
                'status',
                $blockingStatuses
            )
            ->when(
                $ignoreBookingId !== null,
                fn ($query) =>
                $query->whereKeyNot($ignoreBookingId)
            )
            ->get([
                'id',
                'start_time',
                'end_time',
            ]);

        $slots = [];

        /*
        |--------------------------------------------------------------------------
        | Generate slots
        |--------------------------------------------------------------------------
        */

        foreach ($workingHours as $workingHour) {
            $workStart = $date
                ->copy()
                ->setTimeFromTimeString(
                    $this->normalizeTime(
                        $workingHour->start_time
                    )
                );

            $workEnd = $date
                ->copy()
                ->setTimeFromTimeString(
                    $this->normalizeTime(
                        $workingHour->end_time
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Ignore invalid intervals
            |--------------------------------------------------------------------------
            */

            if ($workEnd->lte($workStart)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Generate 15-minute slots
            |--------------------------------------------------------------------------
            */

            for (
                $cursor = $workStart->copy();

                $cursor
                    ->copy()
                    ->addMinutes($duration)
                    ->lte($workEnd);

                $cursor->addMinutes(
                    self::SLOT_INTERVAL_MINUTES
                )
            ) {
                $slotStart = $cursor->copy();

                $slotEnd = $cursor
                    ->copy()
                    ->addMinutes($duration);

                /*
                |--------------------------------------------------------------------------
                | Don't expose past slots for today
                |--------------------------------------------------------------------------
                */

                if (
                    $date->isToday() &&
                    $slotStart->lte(now())
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Detect booking overlap
                |--------------------------------------------------------------------------
                */

                $overlap = $blockedBookings->contains(
                    function ($booking) use (
                        $date,
                        $slotStart,
                        $slotEnd
                    ): bool {
                        $bookingStart = $date
                            ->copy()
                            ->setTimeFromTimeString(
                                $this->normalizeTime(
                                    $booking->start_time
                                )
                            );

                        $bookingEnd = $date
                            ->copy()
                            ->setTimeFromTimeString(
                                $this->normalizeTime(
                                    $booking->end_time
                                )
                            );

                        return
                            $bookingStart < $slotEnd &&
                            $bookingEnd > $slotStart;
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Slot response
                |--------------------------------------------------------------------------
                */

                $slots[] = [
                    'start' => $slotStart->format('H:i'),

                    'end' => $slotEnd->format('H:i'),

                    'available' => !$overlap,

                    'status' => $overlap
                        ? 'booked'
                        : 'available',

                    'label' => $overlap
                        ? 'رزرو شده'
                        : 'آزاد',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sort slots
        |--------------------------------------------------------------------------
        */

        usort(
            $slots,
            fn (array $a, array $b): int =>
            strcmp(
                $a['start'],
                $b['start']
            )
        );

        return $slots;
    }

    /**
     * Normalize database time values.
     */
    private function normalizeTime(
        mixed $value
    ): string {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i:s');
        }

        return Carbon::parse(
            (string) $value
        )->format('H:i:s');
    }
}
