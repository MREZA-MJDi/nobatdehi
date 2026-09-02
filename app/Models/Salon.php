<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salon extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',

        'owner_id',

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
    | Owner
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Creator
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
    | Route Binding
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function services(): HasMany
    {
        return $this->hasMany(
            Service::class,
            'salon_id'
        );
    }

    /**
     * @return HasMany
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(
            WorkingHour::class,
            'salon_id'
        )->orderBy('day_of_week')
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class,
            'salon_id'
        );
    }
}
