<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\Booking\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ManualBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_booking_records_contact_snapshot_without_creating_or_linking_customer_account(): void
    {
        Event::fake();

        [$owner, $salon, $barber, $service, $date] = $this->fixture();

        $existingCustomer = User::create([
            'name' => 'Existing Account',
            'phone' => '09120000009',
            'email' => 'existing-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $beforeUsers = User::count();

        $booking = app(BookingService::class)->createManual(
            $owner,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '10:00',
                'customer_name' => 'رضا رضایی',
                'customer_phone' => $existingCustomer->phone,
            ]
        );

        $this->assertSame($beforeUsers, User::count());

        $booking->refresh();

        $this->assertNull($booking->customer_id);
        $this->assertSame('رضا رضایی', $booking->customer_name);
        $this->assertSame($existingCustomer->phone, $booking->customer_phone);
        $this->assertTrue($booking->is_manual);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->status);
    }

    public function test_manual_booking_requires_owner_of_the_target_salon(): void
    {
        Event::fake();

        [$owner, $salon, $barber, $service, $date] = $this->fixture();

        $otherOwner = User::create([
            'name' => 'Other Owner',
            'phone' => '09120000010',
            'email' => 'other-owner-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'salon_owner',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingService::class)->createManual(
            $otherOwner,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '10:00',
                'customer_name' => 'Test Customer',
                'customer_phone' => '09121111111',
            ]
        );
    }

    public function test_manual_booking_does_not_require_customer_id(): void
    {
        Event::fake();

        [$owner, $salon, $barber, $service, $date] = $this->fixture();

        $booking = app(BookingService::class)->createManual(
            $owner,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '10:00',
                'customer_name' => 'مشتری حضوری',
                'customer_phone' => '09122222222',
            ]
        );

        $this->assertNull($booking->customer_id);
        $this->assertSame('مشتری حضوری', $booking->customer_name);
        $this->assertSame('09122222222', $booking->customer_phone);
    }

    private function fixture(): array
    {
        $owner = User::create([
            'name' => 'Salon Owner',
            'phone' => '09120000001',
            'email' => 'owner-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'salon_owner',
        ]);

        $salon = Salon::create([
            'name' => 'Manual Booking Salon',
            'slug' => 'manual-booking-salon-' . uniqid(),
            'code' => 'MAN-' . random_int(1000, 9999),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $barber = Barber::create([
            'salon_id' => $salon->id,
            'name' => 'Manual Barber',
            'phone' => '09120000002',
            'is_active' => true,
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Manual Service',
            'duration_minutes' => 60,
            'price' => 120000,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $date = now(config('app.timezone', 'Asia/Tehran'))
            ->addDay()
            ->startOfDay();

        WorkingHour::create([
            'salon_id' => $salon->id,
            'day_of_week' => ($date->dayOfWeek + 1) % 7,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_closed' => false,
            'sort_order' => 0,
        ]);

        return [$owner, $salon, $barber, $service, $date];
    }
}
