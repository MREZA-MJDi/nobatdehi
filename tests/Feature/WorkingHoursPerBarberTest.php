<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkingHoursPerBarberTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_separate_working_hours_for_each_barber(): void
    {
        [$owner, $salon, $barberA, $barberB] = $this->fixture();

        $this->actingAs($owner)
            ->put(route('salon.working-hours.update'), [
                'barber_id' => $barberA->id,
                'hours' => $this->hoursPayload('10:00', '18:00'),
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($owner)
            ->put(route('salon.working-hours.update'), [
                'barber_id' => $barberB->id,
                'hours' => $this->hoursPayload('12:00', '20:00'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('working_hours', [
            'salon_id' => $salon->id,
            'barber_id' => $barberA->id,
            'day_of_week' => 0,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'is_closed' => false,
        ]);

        $this->assertDatabaseHas('working_hours', [
            'salon_id' => $salon->id,
            'barber_id' => $barberB->id,
            'day_of_week' => 0,
            'start_time' => '12:00:00',
            'end_time' => '20:00:00',
            'is_closed' => false,
        ]);
    }

    public function test_availability_uses_barber_schedule_and_keeps_salon_fallback(): void
    {
        [$owner, $salon, $barberA, $barberB, $service, $date] = $this->fixture(true);

        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

        WorkingHour::create([
            'salon_id' => $salon->id,
            'barber_id' => $barberA->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '12:00',
            'end_time' => '16:00',
            'is_closed' => false,
            'sort_order' => 0,
        ]);

        $availability = app(AvailabilityService::class);

        $barberASlots = $availability->slots($salon, $barberA, $service, $date);
        $barberBSlots = $availability->slots($salon, $barberB, $service, $date);

        $this->assertNotNull(collect($barberASlots)->firstWhere('start', '12:00'));
        $this->assertNull(collect($barberASlots)->firstWhere('start', '10:00'));

        $this->assertNotNull(collect($barberBSlots)->firstWhere('start', '10:00'));
        $this->assertNotNull(collect($barberBSlots)->firstWhere('start', '18:00'));
    }

    public function test_custom_closed_day_for_barber_does_not_fall_back_to_salon(): void
    {
        [$owner, $salon, $barberA, $barberB, $service, $date] = $this->fixture(true);

        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

        WorkingHour::create([
            'salon_id' => $salon->id,
            'barber_id' => $barberA->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => null,
            'end_time' => null,
            'is_closed' => true,
            'sort_order' => 0,
        ]);

        $slots = app(AvailabilityService::class)->slots(
            $salon,
            $barberA,
            $service,
            $date
        );

        $this->assertSame([], $slots);
    }

    private function hoursPayload(string $start, string $end): array
    {
        return collect(range(0, 6))
            ->mapWithKeys(function (int $day) use ($start, $end): array {
                return [
                    $day => [
                        'day_of_week' => $day,
                        'is_closed' => $day === 6 ? 1 : 0,
                        'intervals' => $day === 6
                            ? []
                            : [
                                [
                                    'start_time' => $start,
                                    'end_time' => $end,
                                ],
                            ],
                    ],
                ];
            })
            ->all();
    }

    private function fixture(bool $withService = false): array
    {
        $owner = User::create([
            'name' => 'Owner',
            'phone' => '09120000001',
            'email' => 'owner-working-hours@example.test',
            'password' => 'password',
            'role' => 'salon_owner',
        ]);

        $salon = Salon::create([
            'name' => 'Schedule Salon',
            'slug' => 'schedule-salon-' . uniqid(),
            'code' => 'SCH-' . random_int(1000, 9999),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $barberA = Barber::create([
            'salon_id' => $salon->id,
            'name' => 'الهام',
            'phone' => '09120000002',
            'is_active' => true,
        ]);

        $barberB = Barber::create([
            'salon_id' => $salon->id,
            'name' => 'مریم',
            'phone' => '09120000003',
            'is_active' => true,
        ]);

        $service = $withService
            ? Service::create([
                'salon_id' => $salon->id,
                'name' => 'Haircut',
                'duration_minutes' => 60,
                'price' => 100000,
                'is_active' => true,
                'sort_order' => 0,
            ])
            : null;

        if ($withService) {
            $date = Carbon::now(config('app.timezone', 'Asia/Tehran'))
                ->addDays(7)
                ->startOfDay();

            WorkingHour::create([
                'salon_id' => $salon->id,
                'barber_id' => null,
                'day_of_week' => ($date->dayOfWeek + 1) % 7,
                'start_time' => '09:00',
                'end_time' => '21:00',
                'is_closed' => false,
                'sort_order' => 0,
            ]);
        } else {
            $date = null;
        }

        return [$owner, $salon, $barberA, $barberB, $service, $date];
    }
}
