<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'purpose',
        'code',
        'attempts',
        'expires_at',
        'consumed_at',
        'sent_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
