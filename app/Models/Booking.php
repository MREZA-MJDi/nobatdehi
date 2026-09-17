<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'barber_id',
        'service_id',
        'customer_id',
        'booking_date',
        'start_time',
        'end_time',
        'price',
        'commission_rate',
        'commission_amount',
        'commission_recipient',
        'commission_status',
        'status',
        'confirmed_by',
        'confirmed_at',
        'priority_overridden',
        'priority_override_reason',
        'notes',
        'status_note',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'price' => 'integer',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'integer',
            'status' => BookingStatus::class,
            'confirmed_at' => 'datetime',
            'priority_overridden' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            if ($booking->commission_rate === null) {
                $booking->commission_rate = (float) config(
                    'booking.commission.rate',
                    10
                );
            }

            if ($booking->commission_amount === null) {
                $booking->commission_amount = self::calculateCommissionAmount(
                    (int) $booking->price,
                    (float) $booking->commission_rate
                );
            }

            if ($booking->commission_recipient === null) {
                $booking->commission_recipient = (string) config(
                    'booking.commission.recipient',
                    'salon'
                );
            }

            if ($booking->commission_status === null) {
                $booking->commission_status = (string) config(
                    'booking.commission.status',
                    'pending'
                );
            }
        });

        static::updating(function (Booking $booking): void {
            if (!$booking->isDirty('price')) {
                return;
            }

            if ($booking->status === BookingStatus::CONFIRMED) {
                return;
            }

            $booking->commission_amount = self::calculateCommissionAmount(
                (int) $booking->price,
                (float) $booking->commission_rate
            );
        });
    }

    private static function calculateCommissionAmount(
        int $price,
        float $rate
    ): int {
        return (int) round(
            max(0, $price) * max(0, $rate) / 100
        );
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
