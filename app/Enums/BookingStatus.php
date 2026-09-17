<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار',
            self::CONFIRMED => 'تأیید شده',
            self::COMPLETED => 'تکمیل شده',
            self::CANCELLED => 'لغو شده',
        };
    }

    /**
     * Only a confirmed booking permanently blocks the time slot.
     * Pending bookings stay visible as a queue and may coexist.
     */
    public function blocksAvailability(): bool
    {
        return $this === self::CONFIRMED;
    }
}
