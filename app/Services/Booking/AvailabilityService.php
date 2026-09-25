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
    /**
     * Minimum grid interval for booking starts.
     *
     * Example:
     * 10:00
     * 10:15
     * 10:30
     * 10:45
     */
    private const SLOT_INTERVAL_MINUTES = 15;

    /**
     * Return all possible booking start times for a barber,
     * service and date.
     */
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
        | Normalize date
        |--------------------------------------------------------------------------
        */

        $date = $date->copy()->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Daily status override
        |--------------------------------------------------------------------------
        |
        | WorkingHours = normal weekly schedule.
        |
        | SalonDailyStatus = exception for a specific date.
        |
        | If that date is closed, the salon is unavailable completely.
        |
        */

        $dailyStatuses = $salon->relationLoaded('dailyStatuses')
            ? collect($salon->getRelation('dailyStatuses'))
            : $salon->dailyStatuses()
                ->whereDate('date', $date->toDateString())
                ->get();

        $dailyStatus = $dailyStatuses->first(
            fn ($status) => Carbon::parse((string) $status->date)->toDateString() === $date->toDateString()
        );

        if (
            $dailyStatus &&
            $dailyStatus->is_closed
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
        | Get weekly working intervals
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Barber-specific schedule with salon fallback
        |--------------------------------------------------------------------------
        |
        | A barber may have an explicit schedule for a day, including a closed
        | row. When no barber-specific rows exist, the salon-wide schedule is
        | used as the default.
        |
        */
        $barberSchedule = $barber
            ->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->get();

        $workingHours = $barberSchedule->isNotEmpty()
            ? $barberSchedule
                ->filter(
                    fn ($workingHour): bool =>
                        ! $workingHour->is_closed &&
                        $workingHour->start_time &&
                        $workingHour->end_time
                )
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['start_time', 'asc'],
                ])
                ->values()
            : $salon
                ->workingHours()
                ->whereNull('barber_id')
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
        |
        | Every status that blocks availability comes directly
        | from the BookingStatus enum.
        |
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
        |
        | IMPORTANT:
        | Manual bookings are stored as CONFIRMED,
        | so they automatically enter this query and block slots.
        |
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
                $query->where(
                    'id',
                    '!=',
                    $ignoreBookingId
                )
            )
            ->get([
                'id',
                'booking_date',
                'start_time',
                'end_time',
                'status',
            ]);

        $slots = [];

        /*
        |--------------------------------------------------------------------------
        | Generate availability
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
            | Invalid interval
            |--------------------------------------------------------------------------
            */

            if ($workEnd->lte($workStart)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Generate 15-minute start grid
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
                | Don't expose past times for today
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
                | Detect overlap
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
                | Add slot
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
        | Sort
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | De-duplicate starts
        |--------------------------------------------------------------------------
        |
        | Legacy data can contain repeated working-hour rows. Availability is a
        | booking contract, so the same start time must never be exposed twice.
        |--------------------------------------------------------------------------
        */

        $slots = collect($slots)
            ->unique(
                fn (array $slot): string =>
                $slot['start'] . '|' . $slot['end']
            )
            ->values()
            ->all();

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
     * Check whether a specific start time is available.
     */
    public function isAvailable(
        Salon $salon,
        Barber $barber,
        Service $service,
        CarbonInterface $date,
        string $startTime,
        ?int $ignoreBookingId = null
    ): bool {
        $selected = collect(
            $this->slots(
                $salon,
                $barber,
                $service,
                $date,
                $ignoreBookingId
            )
        )->firstWhere(
            'start',
            $startTime
        );

        return
            $selected !== null &&
            (bool) ($selected['available'] ?? false);
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
