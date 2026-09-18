<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsInboundMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_message_id',
        'from_phone',
        'body',
        'booking_id',
        'action',
        'status',
        'error',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(
            Booking::class
        );
    }
}
