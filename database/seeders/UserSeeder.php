<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $phone = PhoneNumber::normalize(
            env(
                'SUPER_ADMIN_PHONE',
                '09121234567'
            )
        );

        User::updateOrCreate(
            [
                'phone' => $phone,
            ],
            [
                'name' => env(
                    'SUPER_ADMIN_NAME',
                    'Super Admin'
                ),

                'email' => env(
                    'SUPER_ADMIN_EMAIL',
                    'admin@nobatdehi.test'
                ),

                /*
                |--------------------------------------------------------------------------
                | Authentication
                |--------------------------------------------------------------------------
                |
                | This project uses Phone + OTP authentication.
                | No password is required for Super Admin login.
                |
                */

                'password' => null,

                /*
                |--------------------------------------------------------------------------
                | Role
                |--------------------------------------------------------------------------
                */

                'role' => UserRole::SUPER_ADMIN,

                /*
                |--------------------------------------------------------------------------
                | Verification
                |--------------------------------------------------------------------------
                */

                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );
    }
}
