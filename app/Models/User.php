<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function createdSalons(): HasMany
    {
        return $this->hasMany(
            Salon::class,
            'created_by'
        );
    }

    public function managedSalons(): HasMany
    {
        return $this->hasMany(
            Salon::class,
            'owner_id'
        );
    }

    public function phoneOtps(): HasMany
    {
        return $this->hasMany(
            PhoneOtp::class,
            'phone',
            'phone'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isBarber(): bool
    {
        return $this->role === UserRole::BARBER;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function hasRole(
        UserRole|string $role
    ): bool {
        $role = $role instanceof UserRole
            ? $role
            : UserRole::tryFrom($role);

        return $role !== null
            && $this->role === $role;
    }

    public function hasAnyRole(
        array $roles
    ): bool {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
