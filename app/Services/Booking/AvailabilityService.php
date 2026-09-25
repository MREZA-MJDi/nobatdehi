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
     * 10:30
     * 11:00
     * 11:30
     *
     * Service duration is independent from this start-time grid.
     */
    private const SLOT_INTERVAL_MINUTES = 30;

    /**
     * Resolve the effective working-hours contract for one barber on one date.
     *
     * Rules:
     * - An explicit barber day overrides the salon day, including a closed day.
     * - If the barber has no rows for that day, the salon-wide schedule is used.
     * - Only valid work intervals are exposed.
     * - Multiple identical legacy rows are de-duplicated.
     * - Gaps between work intervals are explicit breaks.
     */
    public function daySchedule(
        Salon $salon,
        Barber $barber,
        CarbonInterface $date
    ): array {
        if (
            (int) $barber->salon_id !== (int) $salon->id ||
            !$salon->is_active ||
            !$barber->is_active
        ) {
            return [
                'status' => 'unavailable',
                'is_closed' => true,
                'intervals' => [],
                'breaks' => [],
                'day_of_week' => null,
                'day_name' => null,
            ];
        }

        $date = $date->copy()->startOfDay();
        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

        $dayNames = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه',
        ];

        $dailyStatus = $salon
            ->dailyStatuses()
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($dailyStatus?->is_closed) {
            return [
                'status' => 'closed',
                'is_closed' => true,
                'intervals' => [],
                'breaks' => [],
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayNames[$dayOfWeek],
            ];
        }

        $barberRows = $barber
            ->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        $dayRows = $barberRows->isNotEmpty()
            ? $barberRows
            : $salon
                ->workingHours()
                ->whereNull('barber_id')
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('sort_order')
                ->orderBy('start_time')
                ->get();

        if ($dayRows->contains(fn ($row) => (bool) $row->is_closed)) {
            return [
                'status' => 'closed',
                'is_closed' => true,
                'intervals' => [],
                'breaks' => [],
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayNames[$dayOfWeek],
            ];
        }

        $intervals = $dayRows
            ->filter(
                fn ($row): bool =>
                    !$row->is_closed &&
                    filled($row->start_time) &&
                    filled($row->end_time)
            )
            ->map(function ($row): ?array {
                try {
                    $start = substr($this->normalizeTime($row->start_time), 0, 5);
                    $end = substr($this->normalizeTime($row->end_time), 0, 5);
                } catch (\Throwable) {
                    return null;
                }

                return $start < $end
                    ? ['start' => $start, 'end' => $end]
                    : null;
            })
            ->filter()
            ->unique(fn (array $interval): string => $interval['start'] . '|' . $interval['end'])
            ->sortBy([
                ['start', 'asc'],
                ['end', 'asc'],
            ])
            ->values()
            ->all();

        if ($intervals === []) {
            return [
                'status' => 'not_configured',
                'is_closed' => false,
                'intervals' => [],
                'breaks' => [],
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayNames[$dayOfWeek],
            ];
        }

        $breaks = [];

        for ($index = 1, $count = count($intervals); $index < $count; $index++) {
            $previous = $intervals[$index - 1];
            $current = $intervals[$index];

            if ($previous['end'] < $current['start']) {
                $breaks[] = [
                    'start' => $previous['end'],
                    'end' => $current['start'],
                ];
            }
        }

        return [
            'status' => 'open',
            'is_closed' => false,
            'intervals' => $intervals,
            'breaks' => $breaks,
            'day_of_week' => $dayOfWeek,
            'day_name' => $dayNames[$dayOfWeek],
        ];
    }

    /**
     * Return all possible booking start times for a barber,
     * service and date.
     */
    public function slots(
        Salon $salon,
        Barber $barber,
        Service $service,
        CarbonInterface $date,
        ?int $ignoreBookingId = null,
        ?int $customerId = null
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

        $schedule = $this->daySchedule($salon, $barber, $date);

        if ($schedule['status'] !== 'open') {
            return [];
        }

        $workingHours = $schedule['intervals'];

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

        /*
        |--------------------------------------------------------------------------
        | Pending priority notices
        |--------------------------------------------------------------------------
        |
        | PENDING bookings do not block the booking grid. They only carry
        | temporal priority: an existing pending request is earlier than a
        | newly submitted request and should be disclosed to that customer.
        |
        */
        $pendingBookings = $barber
            ->bookings()
            ->whereDate(
                'booking_date',
                $date->toDateString()
            )
            ->where('status', BookingStatus::PENDING->value)
            ->when(
                $ignoreBookingId !== null,
                fn ($query) =>
                $query->where(
                    'id',
                    '!=',
                    $ignoreBookingId
                )
            )
            ->when(
                $customerId !== null,
                fn ($query) =>
                $query->where(function ($query) use ($customerId) {
                    $query
                        ->whereNull('customer_id')
                        ->orWhere('customer_id', '!=', $customerId);
                })
            )
            ->orderBy('created_at')
            ->orderBy('id')
            ->get([
                'id',
                'customer_id',
                'start_time',
                'end_time',
                'created_at',
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
                    $workingHour['start'] . ':00'
                );

            $workEnd = $date
                ->copy()
                ->setTimeFromTimeString(
                    $workingHour['end'] . ':00'
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
            | Generate 30-minute booking start grid
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | Align booking starts to the global half-hour grid.
            |--------------------------------------------------------------------------
            |
            | Working hours and breaks may still be defined in 15-minute
            | precision, but customer/manual booking starts are intentionally
            | limited to :00 and :30.
            |
            */
            $cursor = $workStart->copy();

            $minuteOffset = $cursor->minute % self::SLOT_INTERVAL_MINUTES;

            if ($minuteOffset !== 0) {
                $cursor->addMinutes(
                    self::SLOT_INTERVAL_MINUTES - $minuteOffset
                );
            }

            for (
                ;
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

                $hasEarlierPending = $pendingBookings->contains(
                    function ($booking) use (
                        $date,
                        $slotStart,
                        $slotEnd
                    ): bool {
                        $bookingStart = $date
                            ->copy()
                            ->setTimeFromTimeString(
                                $this->normalizeTime($booking->start_time)
                            );

                        $bookingEnd = $date
                            ->copy()
                            ->setTimeFromTimeString(
                                $this->normalizeTime($booking->end_time)
                            );

                        return
                            $bookingStart < $slotEnd &&
                            $bookingEnd > $slotStart;
                    }
                );

                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'available' => !$overlap,
                    'status' => $overlap ? 'booked' : 'available',
                    'label' => $overlap ? 'رزرو شده' : 'آزاد',
                    'pending_priority_conflict' => !$overlap && $hasEarlierPending,
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
