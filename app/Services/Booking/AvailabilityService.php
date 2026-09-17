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
     */
    private const SLOT_INTERVAL_MINUTES = 15;

    /**
     * Return all possible booking start times for a barber,
     * service and date.
     *
     * Confirmed bookings block a slot.
     * Pending bookings do not block it; they are exposed as queue metadata.
     */
    public function slots(
        Salon $salon,
        Barber $barber,
        Service $service,
        CarbonInterface $date,
        ?int $ignoreBookingId = null
    ): array {
        if (
            (int) $barber->salon_id !== (int) $salon->id ||
            (int) $service->salon_id !== (int) $salon->id
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

        $date = $date->copy()->startOfDay();

        $dailyStatus = $salon
            ->dailyStatuses()
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($dailyStatus && $dailyStatus->is_closed) {
            return [];
        }

        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

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

        $duration = max(1, (int) $service->duration_minutes);

        $blockingStatuses = collect(BookingStatus::cases())
            ->filter(
                fn (BookingStatus $status): bool => $status->blocksAvailability()
            )
            ->map(
                fn (BookingStatus $status): string => $status->value
            )
            ->values()
            ->all();

        $confirmedBookings = $barber
            ->bookings()
            ->whereDate('booking_date', $date->toDateString())
            ->whereIn('status', $blockingStatuses)
            ->when(
                $ignoreBookingId !== null,
                fn ($query) => $query->where('id', '!=', $ignoreBookingId)
            )
            ->get(['id', 'start_time', 'end_time']);

        $pendingBookings = $barber
            ->bookings()
            ->whereDate('booking_date', $date->toDateString())
            ->where('status', BookingStatus::PENDING->value)
            ->when(
                $ignoreBookingId !== null,
                fn ($query) => $query->where('id', '!=', $ignoreBookingId)
            )
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'start_time', 'end_time', 'created_at']);

        $slots = [];

        foreach ($workingHours as $workingHour) {
            $workStart = $date
                ->copy()
                ->setTimeFromTimeString($this->normalizeTime($workingHour->start_time));

            $workEnd = $date
                ->copy()
                ->setTimeFromTimeString($this->normalizeTime($workingHour->end_time));

            if ($workEnd->lte($workStart)) {
                continue;
            }

            for (
                $cursor = $workStart->copy();
                $cursor->copy()->addMinutes($duration)->lte($workEnd);
                $cursor->addMinutes(self::SLOT_INTERVAL_MINUTES)
            ) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);

                if ($date->isToday() && $slotStart->lte(now()) ) {
                    continue;
                }

                $overlaps = function ($booking) use ($date, $slotStart, $slotEnd): bool {
                    $bookingStart = $date
                        ->copy()
                        ->setTimeFromTimeString($this->normalizeTime($booking->start_time));

                    $bookingEnd = $date
                        ->copy()
                        ->setTimeFromTimeString($this->normalizeTime($booking->end_time));

                    return $bookingStart < $slotEnd && $bookingEnd > $slotStart;
                };

                $confirmedOverlap = $confirmedBookings->contains($overlaps);
                $slotPendingBookings = $pendingBookings
                    ->filter($overlaps)
                    ->values();

                $pendingCount = $slotPendingBookings->count();
                $oldestPending = $slotPendingBookings->first();

                $status = $confirmedOverlap
                    ? 'confirmed'
                    : ($pendingCount > 0 ? 'pending' : 'available');

                $label = match ($status) {
                    'confirmed' => 'تأیید شده',
                    'pending' => 'در انتظار تأیید',
                    default => 'آزاد',
                };

                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'available' => !$confirmedOverlap,
                    'status' => $status,
                    'label' => $label,
                    'pending_count' => $pendingCount,
                    'oldest_pending_at' => $oldestPending?->created_at?->toIso8601String(),
                ];
            }
        }

        usort(
            $slots,
            fn (array $a, array $b): int => strcmp($a['start'], $b['start'])
        );

        return $slots;
    }

    /**
     * Check whether a specific start time can accept another booking request.
     * Pending requests intentionally remain selectable; only confirmed bookings block.
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
        )->firstWhere('start', $startTime);

        return $selected !== null && (bool) ($selected['available'] ?? false);
    }

    /**
     * Normalize database time values.
     */
    private function normalizeTime(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i:s');
        }

        return Carbon::parse((string) $value)->format('H:i:s');
    }
}
