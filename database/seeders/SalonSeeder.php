<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use F9WebLtd\QrCode\Facades\QrCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SalonSeeder extends Seeder
{
    public function run(): void
    {
        $salons = [
            [
                'name' => 'سالن زیبایی نوبان',
                'slug' => 'salon-noban',
                'code' => 'SALON-NOBAN',
                'description' => 'سالن زیبایی مدرن با خدمات تخصصی مو، میکاپ و زیبایی.',
                'phone' => '09121112233',
                'email' => 'noban@example.test',
                'province' => 'تهران',
                'city' => 'تهران',
                'district' => 'سعادت‌آباد',
                'address' => 'سعادت‌آباد، بلوار اصلی، پلاک ۱۲',
                'latitude' => 35.7721000,
                'longitude' => 51.3754000,
                'primary_color' => '#6757E8',
                'secondary_color' => '#37B8C8',
                'owner_name' => 'مدیر سالن نوبان',
                'owner_phone' => '09121112233',
                'owner_password' => '12345678',
                'services' => [
                    [
                        'name' => 'کوتاهی و براشینگ',
                        'duration_minutes' => 60,
                        'price' => 450000,
                    ],
                    [
                        'name' => 'رنگ و مش',
                        'duration_minutes' => 120,
                        'price' => 1500000,
                    ],
                    [
                        'name' => 'میکاپ',
                        'duration_minutes' => 90,
                        'price' => 900000,
                    ],
                ],
                'barbers' => [
                    [
                        'name' => 'نگار',
                        'specialty' => 'رنگ و لایت',
                    ],
                    [
                        'name' => 'سارا',
                        'specialty' => 'میکاپ و شینیون',
                    ],
                ],
            ],

            [
                'name' => 'خانه زیبایی آناهیتا',
                'slug' => 'anahita-beauty',
                'code' => 'SALON-ANAHI',
                'description' => 'خدمات تخصصی زیبایی با رزرو آنلاین و زمان‌بندی دقیق.',
                'phone' => '09123334455',
                'email' => 'anahita@example.test',
                'province' => 'تهران',
                'city' => 'تهران',
                'district' => 'ونک',
                'address' => 'ونک، خیابان ملاصدرا، پلاک ۲۴',
                'latitude' => 35.7576000,
                'longitude' => 51.4084000,
                'primary_color' => '#A855F7',
                'secondary_color' => '#EC4899',
                'owner_name' => 'مدیر خانه آناهیتا',
                'owner_phone' => '09123334455',
                'owner_password' => '12345678',
                'services' => [
                    [
                        'name' => 'پاکسازی پوست',
                        'duration_minutes' => 60,
                        'price' => 650000,
                    ],
                    [
                        'name' => 'کراتین',
                        'duration_minutes' => 150,
                        'price' => 2200000,
                    ],
                    [
                        'name' => 'شینیون',
                        'duration_minutes' => 90,
                        'price' => 850000,
                    ],
                ],
                'barbers' => [
                    [
                        'name' => 'الهام',
                        'specialty' => 'احیای مو',
                    ],
                    [
                        'name' => 'مریم',
                        'specialty' => 'شینیون',
                    ],
                ],
            ],

            [
                'name' => 'آرایشگاه ماه‌رخ',
                'slug' => 'maahrokh',
                'code' => 'SALON-MHRKH',
                'description' => 'محیطی آرام برای خدمات حرفه‌ای مو و زیبایی بانوان.',
                'phone' => '09125556677',
                'email' => 'maahrokh@example.test',
                'province' => 'تهران',
                'city' => 'تهران',
                'district' => 'پونک',
                'address' => 'پونک، میدان عدل، کوچه سوم، پلاک ۸',
                'latitude' => 35.7552000,
                'longitude' => 51.3268000,
                'primary_color' => '#0EA5E9',
                'secondary_color' => '#14B8A6',
                'owner_name' => 'مدیر آرایشگاه ماه‌رخ',
                'owner_phone' => '09125556677',
                'owner_password' => '12345678',
                'services' => [
                    [
                        'name' => 'کوتاهی مو',
                        'duration_minutes' => 45,
                        'price' => 300000,
                    ],
                    [
                        'name' => 'هایلایت',
                        'duration_minutes' => 120,
                        'price' => 1400000,
                    ],
                    [
                        'name' => 'براشینگ',
                        'duration_minutes' => 45,
                        'price' => 280000,
                    ],
                ],
                'barbers' => [
                    [
                        'name' => 'مهسا',
                        'specialty' => 'کوتاهی و براشینگ',
                    ],
                    [
                        'name' => 'ترانه',
                        'specialty' => 'هایلایت و رنگ',
                    ],
                ],
            ],
        ];


        foreach ($salons as $data) {

            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */

            $owner = User::updateOrCreate(
                [
                    'phone' =>
                        $data['owner_phone'],
                ],
                [
                    'name' =>
                        $data['owner_name'],

                    'email' =>
                        null,

                    'password' =>
                        $data['owner_password'],

                    'role' =>
                        UserRole::SALON_OWNER,

                    'phone_verified_at' =>
                        now(),

                    'email_verified_at' =>
                        null,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Salon
            |--------------------------------------------------------------------------
            */

            $salon = Salon::updateOrCreate(
                [
                    'code' =>
                        $data['code'],
                ],
                [
                    'name' =>
                        $data['name'],

                    'slug' =>
                        $data['slug'],

                    'description' =>
                        $data['description'],

                    'owner_id' =>
                        $owner->id,

                    'phone' =>
                        $data['phone'],

                    'email' =>
                        $data['email'],

                    'primary_color' =>
                        $data['primary_color'],

                    'secondary_color' =>
                        $data['secondary_color'],

                    'province' =>
                        $data['province'],

                    'city' =>
                        $data['city'],

                    'district' =>
                        $data['district'],

                    'address' =>
                        $data['address'],

                    'latitude' =>
                        $data['latitude'],

                    'longitude' =>
                        $data['longitude'],

                    'is_active' =>
                        true,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            $salon->services()->delete();

            foreach (
                $data['services']
                as $index => $service
            ) {
                Service::create([
                    'salon_id' =>
                        $salon->id,

                    'name' =>
                        $service['name'],

                    'duration_minutes' =>
                        $service['duration_minutes'],

                    'price' =>
                        $service['price'],

                    'is_active' =>
                        true,

                    'sort_order' =>
                        $index,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Barbers
            |--------------------------------------------------------------------------
            */

            $salon->barbers()->delete();

            foreach (
                $data['barbers']
                as $barber
            ) {
                Barber::create([
                    'salon_id' =>
                        $salon->id,

                    'name' =>
                        $barber['name'],

                    'specialty' =>
                        $barber['specialty'],

                    'phone' =>
                        null,

                    'bio' =>
                        null,

                    'image_path' =>
                        null,

                    'is_active' =>
                        true,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Working Hours
            |--------------------------------------------------------------------------
            */

            $salon->workingHours()->delete();

            for ($day = 0; $day <= 6; $day++) {

                WorkingHour::create([
                    'salon_id' =>
                        $salon->id,

                    'day_of_week' =>
                        $day,

                    'start_time' =>
                        $day === 6
                            ? null
                            : '10:00',

                    'end_time' =>
                        $day === 6
                            ? null
                            : '20:00',

                    'is_closed' =>
                        $day === 6,

                    'sort_order' =>
                        0,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | QR
            |--------------------------------------------------------------------------
            */

            /*
 |--------------------------------------------------------------------------
 | Generate QR
 |--------------------------------------------------------------------------
 */

            $publicUrl = route(
                'public.salons.show',
                $salon
            );

            $qrPath =
                'salons/qr/' .
                $salon->slug .
                '.svg';

            $qrContents = QrCode::format('svg')
                ->size(800)
                ->margin(2)
                ->generate(
                    $publicUrl
                );

            Storage::disk('public')->put(
                $qrPath,
                $qrContents
            );


            /*
            |--------------------------------------------------------------------------
            | Save QR Path
            |--------------------------------------------------------------------------
            */

            $salon->update([
                'qr_code_path' => $qrPath,
            ]);
        }
    }
}
