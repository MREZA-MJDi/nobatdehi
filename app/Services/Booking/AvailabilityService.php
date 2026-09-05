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
        CarbonInterface $date
    ): array {
        if (
            $barber->salon_id !== $salon->id ||
            $service->salon_id !== $salon->id
        ) {
            return [];
        }

        if (
            !$salon->is_active ||
            !$barber->is_active ||
            !$service->is_active
        ) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Persian week
        |--------------------------------------------------------------------------
        |
        | Carbon:
        | Sunday = 0
        | Monday = 1
        | ...
        | Saturday = 6
        |
        | Application:
        | Saturday = 0
        | Sunday = 1
        | ...
        | Friday = 6
        |
        */

        $dayOfWeek =
            ($date->dayOfWeek + 1) % 7;

        /*
        |--------------------------------------------------------------------------
        | Get ALL working intervals for this day
        |--------------------------------------------------------------------------
        */

        $workingHours = $salon
            ->workingHours()
            ->where(
                'day_of_week',
                $dayOfWeek
            )
            ->where(
                'is_closed',
                false
            )
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
                [
                    BookingStatus::PENDING->value,
                    BookingStatus::CONFIRMED->value,
                ]
            )
            ->get([
                'start_time',
                'end_time',
            ]);

        $slots = [];

        /*
        |--------------------------------------------------------------------------
        | Generate slots for every working interval
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

            if ($workEnd->lte($workStart)) {
                continue;
            }

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
                | Don't show past times for today
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
                | Check booking overlap
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

                $slots[] = [
                    'start' =>
                        $slotStart->format('H:i'),

                    'end' =>
                        $slotEnd->format('H:i'),

                    'available' =>
                        !$overlap,

                    'status' =>
                        $overlap
                            ? 'booked'
                            : 'available',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sort final slots by time
        |--------------------------------------------------------------------------
        */

        usort(
            $slots,
            fn (array $a, array $b) =>
            strcmp($a['start'], $b['start'])
        );

        return $slots;
    }

    private function normalizeTime(
        mixed $value
    ): string {
        if (
            $value instanceof CarbonInterface
        ) {
            return $value->format('H:i:s');
        }

        return Carbon::parse(
            (string) $value
        )->format('H:i:s');
    }
}
