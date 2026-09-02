<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'phone' => env(
                    'SUPER_ADMIN_PHONE',
                    '09121234567'
                ),
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

                'password' => null,

                'role' =>
                    UserRole::SUPER_ADMIN,

                'phone_verified_at' =>
                    now(),

                'email_verified_at' =>
                    now(),
            ]
        );
    }
}
