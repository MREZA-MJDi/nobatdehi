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
        'status',
        'notes',
    ];


    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'price' => 'integer',
            'status' => BookingStatus::class,
        ];
    }


    public function salon(): BelongsTo
    {
        return $this->belongsTo(
            Salon::class
        );
    }


    public function barber(): BelongsTo
    {
        return $this->belongsTo(
            Barber::class
        );
    }


    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class
        );
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }


    public function review(): HasOne
    {
        return $this->hasOne(
            Review::class
        );
    }
}
