<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salon_id',
        'bio',
        'specialty',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Salon
    |--------------------------------------------------------------------------
    */

    public function salon(): BelongsTo
    {
        return $this->belongsTo(
            Salon::class,
            'salon_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Manager
    |--------------------------------------------------------------------------
    */

    public function managedSalon(): HasMany
    {
        return $this->hasMany(
            Salon::class,
            'manager_barber_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'بدون نام';
    }
}
