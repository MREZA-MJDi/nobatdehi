<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Events\BookingCreated;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_blocks_overlapping_pending_and_confirmed_bookings(): void
    {
        Event::fake();

        [$owner, $customer, $salon, $barber, $service, $date] =
            $this->fixture();

        Booking::create([
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'price' => 100000,
            'status' => BookingStatus::PENDING,
        ]);

        $slots = app(AvailabilityService::class)->slots(
            $salon,
            $barber,
            $service,
            $date
        );

        $slot = collect($slots)->firstWhere('start', '10:00');

        $this->assertNotNull($slot);
        $this->assertFalse($slot['available']);
        $this->assertSame('booked', $slot['status']);
    }

    public function test_availability_uses_half_hour_start_grid_and_preserves_service_duration(): void
    {
        [$owner, $customer, $salon, $barber, $service, $date] =
            $this->fixture();

        $service->update([
            'duration_minutes' => 45,
        ]);

        $slots = app(AvailabilityService::class)->slots(
            $salon,
            $barber,
            $service,
            $date
        );

        $slot0900 = collect($slots)->firstWhere('start', '09:00');
        $slot0930 = collect($slots)->firstWhere('start', '09:30');

        $this->assertNotNull($slot0900);
        $this->assertSame('09:45', $slot0900['end']);

        $this->assertNotNull($slot0930);
        $this->assertSame('10:15', $slot0930['end']);

        $this->assertNull(collect($slots)->firstWhere('start', '09:15'));
        $this->assertNull(collect($slots)->firstWhere('start', '09:45'));
    }

    public function test_booking_start_off_half_hour_grid_is_rejected_by_availability(): void
    {
        Event::fake();

        [$owner, $customer, $salon, $barber, $service, $date] =
            $this->fixture();

        $this->expectException(ValidationException::class);

        app(BookingService::class)->create(
            $customer,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '09:15',
            ]
        );
    }

    public function test_manual_confirmed_booking_blocks_customer_availability(): void
    {
        Event::fake();

        [$owner, $customer, $salon, $barber, $service, $date] =
            $this->fixture();

        $booking = app(BookingService::class)->createManual(
            $owner,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '11:00',
                'customer_name' => 'Manual Customer',
                'customer_phone' => '09123334444',
            ]
        );

        $this->assertSame(BookingStatus::CONFIRMED, $booking->status);
        $this->assertTrue($booking->is_manual);

        $slots = app(AvailabilityService::class)->slots(
            $salon,
            $barber,
            $service,
            $date
        );

        $slot = collect($slots)->firstWhere('start', '11:00');

        $this->assertNotNull($slot);
        $this->assertFalse($slot['available']);
        $this->assertSame('booked', $slot['status']);
    }

    public function test_customer_cannot_double_book_same_barber_and_time(): void
    {
        Event::fake();

        [$owner, $customer, $salon, $barber, $service, $date] =
            $this->fixture();

        $service->update([
            'duration_minutes' => 60,
        ]);

        $bookingService = app(BookingService::class);

        $bookingService->create(
            $customer,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '10:00',
            ]
        );

        $this->expectException(ValidationException::class);

        $bookingService->create(
            $customer,
            [
                'salon_id' => $salon->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => '10:00',
            ]
        );
    }

    public function test_cancelled_booking_does_not_block_availability(): void
    {
        Event::fake();

        [$owner, $customer, $salon, $barber, $service, $date] =
            $this->fixture();

        Booking::create([
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'price' => 100000,
            'status' => BookingStatus::CANCELLED,
        ]);

        $slots = app(AvailabilityService::class)->slots(
            $salon,
            $barber,
            $service,
            $date
        );

        $slot = collect($slots)->firstWhere('start', '10:00');

        $this->assertNotNull($slot);
        $this->assertTrue($slot['available']);
    }

    private function fixture(): array
    {
        $owner = User::create([
            'name' => 'Owner',
            'phone' => '09120000001',
            'email' => 'owner@example.test',
            'password' => 'password',
            'role' => 'salon_owner',
        ]);

        $customer = User::create([
            'name' => 'Customer',
            'phone' => '09120000002',
            'email' => 'customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $salon = Salon::create([
            'name' => 'Test Salon',
            'slug' => 'test-salon-' . uniqid(),
            'code' => 'TEST-' . random_int(1000, 9999),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $barber = Barber::create([
            'salon_id' => $salon->id,
            'name' => 'Test Barber',
            'phone' => '09120000003',
            'is_active' => true,
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 100000,
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

        return [
            $owner,
            $customer,
            $salon,
            $barber,
            $service,
            $date,
        ];
    }
}
