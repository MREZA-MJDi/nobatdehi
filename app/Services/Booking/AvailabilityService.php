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

    public function slots(Salon $salon, Barber $barber, Service $service, CarbonInterface $date): array
    {
        if ($barber->salon_id !== $salon->id || $service->salon_id !== $salon->id) return [];
        if (! $salon->is_active || ! $barber->is_active || ! $service->is_active) return [];

        // Project convention: 0=Saturday ... 6=Friday.
        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

        $workingHour = $salon->workingHours()->where('day_of_week', $dayOfWeek)->first();
        if (! $workingHour || $workingHour->is_closed || ! $workingHour->start_time || ! $workingHour->end_time) return [];

        $workStart = $date->copy()->setTimeFromTimeString($this->normalizeTime($workingHour->start_time));
        $workEnd = $date->copy()->setTimeFromTimeString($this->normalizeTime($workingHour->end_time));
        $duration = max(1, (int) $service->duration_minutes);

        $blockedBookings = $barber->bookings()
            ->whereDate('booking_date', $date->toDateString())
            ->whereIn('status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value])
            ->get(['start_time','end_time']);

        $slots = [];
        $cursor = $workStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($workEnd)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($duration);

            if ($date->isToday() && $slotStart->lte(now())) {
                $cursor->addMinutes(self::SLOT_INTERVAL_MINUTES);
                continue;
            }

            $overlap = $blockedBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                $bookingStart = $slotStart->copy()->setTimeFromTimeString($this->normalizeTime($booking->start_time));
                $bookingEnd = $slotStart->copy()->setTimeFromTimeString($this->normalizeTime($booking->end_time));
                return $bookingStart < $slotEnd && $bookingEnd > $slotStart;
            });

            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'available' => ! $overlap,
                'status' => $overlap ? 'booked' : 'available',
            ];

            $cursor->addMinutes(self::SLOT_INTERVAL_MINUTES);
        }

        return $slots;
    }

    private function normalizeTime(mixed $value): string
    {
        if ($value instanceof CarbonInterface) return $value->format('H:i:s');
        return Carbon::parse((string) $value)->format('H:i:s');
    }
}
