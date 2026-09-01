<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',

        'phone',
        'email',

        'logo_path',
        'cover_path',

        'primary_color',
        'secondary_color',

        'province',
        'city',
        'district',
        'address',

        'latitude',
        'longitude',

        'qr_code_path',

        'manager_barber_id',
        'created_by',

        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',

            'is_active' => 'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Manager Barber
    |--------------------------------------------------------------------------
    */

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            Barber::class,
            'manager_barber_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Creator - Super Admin
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Barbers
    |--------------------------------------------------------------------------
    */

    public function barbers(): HasMany
    {
        return $this->hasMany(
            Barber::class,
            'salon_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return asset(
            'storage/' . $this->logo_path
        );
    }


    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_path) {
            return null;
        }

        return asset(
            'storage/' . $this->cover_path
        );
    }


    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        return asset(
            'storage/' . $this->qr_code_path
        );
    }


    public function getPublicUrlAttribute(): string
    {
        return route(
            'salons.show',
            [
                'salon' => $this->code,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Manager Check
    |--------------------------------------------------------------------------
    */

    public function isManagedBy(
        User $user
    ): bool {
        return $this->manager?->user_id === $user->id;
    }
}
