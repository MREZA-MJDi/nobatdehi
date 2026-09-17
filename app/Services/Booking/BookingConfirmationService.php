<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingNotification;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingConfirmationService
{
    /**
     * Return the pending queue for a concrete barber/time range.
     * Oldest request is first. created_at + id provides deterministic priority.
     */
    public function pendingQueue(Booking $booking): Collection
    {
        return Booking::query()
            ->with('customer:id,name,phone')
            ->where('barber_id', $booking->barber_id)
            ->whereDate('booking_date', $booking->booking_date->toDateString())
            ->where('status', BookingStatus::PENDING->value)
            ->where('id', '!=', $booking->id)
            ->where('start_time', '<', $booking->end_time)
            ->where('end_time', '>', $booking->start_time)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Describe the priority position of a pending booking without mutating it.
     */
    public function priority(Booking $booking): array
    {
        $queue = $this->pendingQueue($booking);
        $ordered = $queue->prepend($booking)->sortBy([
            ['created_at', 'asc'],
            ['id', 'asc'],
        ])->values();

        $position = $ordered->search(
            fn (Booking $item): bool => (int) $item->id === (int) $booking->id
        );

        return [
            'position' => $position === false ? null : $position + 1,
            'pending_count' => $ordered->count(),
            'earlier' => $position !== false
                ? $ordered->slice(0, $position)->values()
                : collect(),
            'queue' => $ordered,
        ];
    }

    /**
     * Confirm a customer booking using a transactional priority check.
     *
     * By default the oldest pending request wins. A later request can only
     * be confirmed by an explicit owner override, which is recorded.
     */
    public function confirm(
        Booking $booking,
        User $actor,
        bool $overridePriority = false,
        ?string $overrideReason = null
    ): Booking {
        return DB::transaction(function () use (
            $booking,
            $actor,
            $overridePriority,
            $overrideReason
        ): Booking {
            $lockedBooking = Booking::query()
                ->with(['salon', 'barber', 'service', 'customer'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $salon = $lockedBooking->salon;

            if (
                !$salon ||
                (int) $salon->owner_id !== (int) $actor->id
            ) {
                abort(403);
            }

            if ($lockedBooking->status !== BookingStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'فقط نوبت‌های در انتظار امکان تأیید دارند.',
                ]);
            }

            // All booking writes for a barber serialize through this lock.
            Barber::query()
                ->whereKey($lockedBooking->barber_id)
                ->lockForUpdate()
                ->firstOrFail();

            $confirmedConflict = Booking::query()
                ->where('barber_id', $lockedBooking->barber_id)
                ->whereDate(
                    'booking_date',
                    $lockedBooking->booking_date->toDateString()
                )
                ->where('status', BookingStatus::CONFIRMED->value)
                ->where('id', '!=', $lockedBooking->id)
                ->where('start_time', '<', $lockedBooking->end_time)
                ->where('end_time', '>', $lockedBooking->start_time)
                ->lockForUpdate()
                ->first();

            if ($confirmedConflict) {
                throw ValidationException::withMessages([
                    'status' => 'این زمان پیش از تأیید شما توسط نوبت دیگری تأیید شده است.',
                ]);
            }

            $earlierPending = Booking::query()
                ->where('barber_id', $lockedBooking->barber_id)
                ->whereDate(
                    'booking_date',
                    $lockedBooking->booking_date->toDateString()
                )
                ->where('status', BookingStatus::PENDING->value)
                ->where('id', '!=', $lockedBooking->id)
                ->where(function ($query) use ($lockedBooking) {
                    $query
                        ->where('start_time', '<', $lockedBooking->end_time)
                        ->where('end_time', '>', $lockedBooking->start_time);
                })
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $olderRequests = $earlierPending
                ->filter(function (Booking $candidate) use ($lockedBooking): bool {
                    if (!$candidate->created_at || !$lockedBooking->created_at) {
                        return (int) $candidate->id < (int) $lockedBooking->id;
                    }

                    if ($candidate->created_at->eq($lockedBooking->created_at)) {
                        return (int) $candidate->id < (int) $lockedBooking->id;
                    }

                    return $candidate->created_at->lt($lockedBooking->created_at);
                })
                ->values();

            if ($olderRequests->isNotEmpty() && !$overridePriority) {
                $first = $olderRequests->first();

                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'این درخواست اولویت اول نیست؛ مشتری «%s» زودتر برای این زمان درخواست ثبت کرده است. برای تأیید با اولویت متفاوت، تأیید دستی را فعال کنید.',
                        $first?->customer?->name ?? 'مشتری دیگر'
                    ),
                ]);
            }

            $reason = $overridePriority
                ? trim((string) $overrideReason)
                : null;

            if ($overridePriority && $reason === '') {
                throw ValidationException::withMessages([
                    'priority_override_reason' => 'دلیل تغییر اولویت را وارد کنید.',
                ]);
            }

            $lockedBooking->update([
                'status' => BookingStatus::CONFIRMED,
                'confirmed_by' => $actor->id,
                'confirmed_at' => now(),
                'priority_overridden' => $overridePriority,
                'priority_override_reason' => $reason,
                'status_note' => $overridePriority
                    ? 'این نوبت با تشخیص صاحب سالن و با تغییر اولویت تأیید شد.'
                    : null,
            ]);

            // Once a slot is confirmed, every competing pending request is resolved.
            $losingBookings = $earlierPending
                ->merge(
                    Booking::query()
                        ->where('barber_id', $lockedBooking->barber_id)
                        ->whereDate(
                            'booking_date',
                            $lockedBooking->booking_date->toDateString()
                        )
                        ->where('status', BookingStatus::PENDING->value)
                        ->where('id', '!=', $lockedBooking->id)
                        ->where('start_time', '<', $lockedBooking->end_time)
                        ->where('end_time', '>', $lockedBooking->start_time)
                        ->get()
                )
                ->unique('id')
                ->values();

            foreach ($losingBookings as $loser) {
                $loser->update([
                    'status' => BookingStatus::CANCELLED,
                    'status_note' => 'این زمان توسط درخواست دیگری تأیید شد و این درخواست از صف خارج شد.',
                ]);

                if ($loser->customer) {
                    $loser->customer->notify(
                        new BookingNotification(
                            $loser->fresh(['service']),
                            'status_changed'
                        )
                    );
                }
            }

            $confirmed = $lockedBooking->fresh([
                'salon',
                'barber',
                'service',
                'customer',
                'confirmer',
            ]);

            if ($confirmed?->customer) {
                $confirmed->customer->notify(
                    new BookingNotification(
                        $confirmed,
                        'status_changed'
                    )
                );
            }

            return $confirmed;
        });
    }

    /**
     * Manual bookings are already confirmed at creation time. This helper
     * resolves any pending customer requests that overlap the newly confirmed
     * manual booking.
     */
    public function reconcileManualConfirmation(
        Booking $booking,
        User $actor
    ): Booking {
        return DB::transaction(function () use ($booking, $actor): Booking {
            $lockedBooking = Booking::query()
                ->with(['salon', 'barber', 'service', 'customer'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if (
                !$lockedBooking->salon ||
                (int) $lockedBooking->salon->owner_id !== (int) $actor->id
            ) {
                abort(403);
            }

            Barber::query()
                ->whereKey($lockedBooking->barber_id)
                ->lockForUpdate()
                ->firstOrFail();

            $losers = Booking::query()
                ->with('customer')
                ->where('barber_id', $lockedBooking->barber_id)
                ->whereDate(
                    'booking_date',
                    $lockedBooking->booking_date->toDateString()
                )
                ->where('status', BookingStatus::PENDING->value)
                ->where('id', '!=', $lockedBooking->id)
                ->where('start_time', '<', $lockedBooking->end_time)
                ->where('end_time', '>', $lockedBooking->start_time)
                ->lockForUpdate()
                ->get();

            foreach ($losers as $loser) {
                $loser->update([
                    'status' => BookingStatus::CANCELLED,
                    'status_note' => 'این زمان توسط سالن به‌صورت دستی تأیید شد و این درخواست از صف خارج شد.',
                ]);

                if ($loser->customer) {
                    $loser->customer->notify(
                        new BookingNotification(
                            $loser->fresh(['service']),
                            'status_changed'
                        )
                    );
                }
            }

            return $lockedBooking->fresh([
                'salon',
                'barber',
                'service',
                'customer',
                'confirmer',
            ]);
        });
    }

    /**
     * Find pending requests for a prospective manual booking.
     */
    public function pendingForSlot(
        Salon $salon,
        Barber $barber,
        Service $service,
        CarbonInterface $date,
        string $startTime
    ): Collection {
        $endTime = $date
            ->copy()
            ->setTimeFromTimeString($startTime)
            ->addMinutes(max(1, (int) $service->duration_minutes))
            ->format('H:i:s');

        return Booking::query()
            ->where('barber_id', $barber->id)
            ->whereDate('booking_date', $date->toDateString())
            ->where('status', BookingStatus::PENDING->value)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->with('customer:id,name,phone')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }
}
