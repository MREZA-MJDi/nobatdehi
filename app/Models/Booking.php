<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

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
