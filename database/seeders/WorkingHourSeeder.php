<?php

namespace Database\Seeders;

use App\Models\Salon;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHourSeeder extends Seeder
{
    public function run(): void
    {
        Salon::query()->each(function (Salon $salon): void {
            $hours = [
                0 => [
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                    'is_closed' => false,
                ],

                1 => [
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                    'is_closed' => false,
                ],

                2 => [
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                    'is_closed' => false,
                ],

                3 => [
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                    'is_closed' => false,
                ],

                4 => [
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                    'is_closed' => false,
                ],

                5 => [
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                    'is_closed' => false,
                ],

                6 => [
                    'start_time' => null,
                    'end_time' => null,
                    'is_closed' => true,
                ],
            ];

            foreach ($hours as $dayOfWeek => $hour) {
                WorkingHour::updateOrCreate(
                    [
                        'salon_id' => $salon->id,
                        'day_of_week' => $dayOfWeek,
                    ],
                    [
                        'start_time' => $hour['start_time'],
                        'end_time' => $hour['end_time'],
                        'is_closed' => $hour['is_closed'],
                        'sort_order' => $dayOfWeek,
                    ]
                );
            }
        });
    }
}
