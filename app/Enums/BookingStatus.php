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
     * آیا این وضعیت باید در محاسبه availability
     * زمان را اشغال‌شده در نظر بگیرد؟
     */
    public function blocksAvailability(): bool
    {
        return match ($this) {
            self::PENDING,
            self::CONFIRMED => true,

            self::COMPLETED,
            self::CANCELLED => false,
        };
    }
}
