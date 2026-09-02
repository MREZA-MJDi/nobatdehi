<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
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
        /*
        |--------------------------------------------------------------------------
        | Ownership / Availability
        |--------------------------------------------------------------------------
        */

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
        | Iranian Week
        |--------------------------------------------------------------------------
        |
        | Carbon:
        | Sunday    = 0
        | Monday    = 1
        | ...
        | Saturday  = 6
        |
        | Application:
        | Saturday  = 0
        | Sunday    = 1
        | ...
        | Friday    = 6
        |
        */

        $dayOfWeek = (
                $date->dayOfWeek + 1
            ) % 7;


        /*
        |--------------------------------------------------------------------------
        | Working Hours
        |--------------------------------------------------------------------------
        */

        $workingHour = $salon
            ->workingHours()
            ->where(
                'day_of_week',
                $dayOfWeek
            )
            ->first();

        if (
            !$workingHour ||
            $workingHour->is_closed ||
            !$workingHour->start_time ||
            !$workingHour->end_time
        ) {
            return [];
        }


        /*
        |--------------------------------------------------------------------------
        | Working Window
        |--------------------------------------------------------------------------
        */

        $workStart = $date
            ->copy()
            ->setTimeFromTimeString(
                $workingHour->start_time
            );

        $workEnd = $date
            ->copy()
            ->setTimeFromTimeString(
                $workingHour->end_time
            );


        $duration =
            (int) $service->duration_minutes;


        /*
        |--------------------------------------------------------------------------
        | Existing Bookings
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


        /*
        |--------------------------------------------------------------------------
        | Generate Slots
        |--------------------------------------------------------------------------
        */

        $slots = [];

        $cursor = $workStart->copy();

        while (
        $cursor
            ->copy()
            ->addMinutes($duration)
            ->lte($workEnd)
        ) {
            $slotStart = $cursor->copy();

            $slotEnd = $cursor
                ->copy()
                ->addMinutes($duration);


            /*
            |--------------------------------------------------------------------------
            | Ignore Past Slots
            |--------------------------------------------------------------------------
            */

            if (
                $date->isToday() &&
                $slotStart->lte(now())
            ) {
                $cursor->addMinutes(
                    self::SLOT_INTERVAL_MINUTES
                );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Collision Detection
            |--------------------------------------------------------------------------
            */

            $overlap = $blockedBookings->contains(
                function ($booking) use (
                    $slotStart,
                    $slotEnd
                ): bool {
                    $bookingStart = $slotStart
                        ->copy()
                        ->setTimeFromTimeString(
                            $booking->start_time
                        );

                    $bookingEnd = $slotStart
                        ->copy()
                        ->setTimeFromTimeString(
                            $booking->end_time
                        );

                    return (
                        $bookingStart < $slotEnd &&
                        $bookingEnd > $slotStart
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Available Slot
            |--------------------------------------------------------------------------
            */

            if (!$overlap) {
                $slots[] = [
                    'start' =>
                        $slotStart->format('H:i'),

                    'end' =>
                        $slotEnd->format('H:i'),
                ];
            }


            $cursor->addMinutes(
                self::SLOT_INTERVAL_MINUTES
            );
        }

        return $slots;
    }
}
