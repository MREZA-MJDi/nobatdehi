<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\WorkingHour;
use App\Models\User;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingConfirmationService;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_requests_can_share_a_slot_without_blocking_availability(): void
    {
        [$owner, $salon, $barber, $service, $date] = $this->makeBookingContext();
        $customerOne = $this->makeCustomer('one');
        $customerTwo = $this->makeCustomer('two');

        $serviceBooking = app(BookingService::class);

        Carbon::setTestNow($date->copy()->setTime(8, 0));
        $first = $serviceBooking->create($customerOne, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow($date->copy()->setTime(8, 5));
        $second = $serviceBooking->create($customerTwo, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow();

        $slot = collect(
            app(AvailabilityService::class)->slots(
                $salon->fresh(),
                $barber->fresh(),
                $service->fresh(),
                $date
            )
        )->firstWhere('start', '10:00');

        $this->assertNotNull($slot);
        $this->assertTrue($slot['available']);
        $this->assertSame('pending', $slot['status']);
        $this->assertSame(2, $slot['pending_count']);
        $this->assertSame($first->created_at->toIso8601String(), $slot['oldest_pending_at']);
        $this->assertSame(10.0, (float) $first->commission_rate);
        $this->assertSame(10_000, $first->commission_amount);
        $this->assertSame(10_000, $second->commission_amount);
    }

    public function test_only_the_oldest_pending_request_can_be_confirmed_without_override(): void
    {
        [$owner, $salon, $barber, $service, $date] = $this->makeBookingContext();
        $firstCustomer = $this->makeCustomer('first');
        $secondCustomer = $this->makeCustomer('second');
        $bookingService = app(BookingService::class);
        $confirmationService = app(BookingConfirmationService::class);

        Carbon::setTestNow($date->copy()->setTime(8, 0));
        $first = $bookingService->create($firstCustomer, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow($date->copy()->setTime(8, 5));
        $second = $bookingService->create($secondCustomer, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow();

        try {
            $confirmationService->confirm($second, $owner);
            $this->fail('A later pending request should require an owner override.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('اولویت اول نیست', $exception->getMessage());
        }

        $confirmationService->confirm($first, $owner);

        $this->assertSame(
            BookingStatus::CONFIRMED,
            $first->fresh()->status
        );

        $this->assertSame(
            BookingStatus::CANCELLED,
            $second->fresh()->status
        );

        $this->assertNotNull($first->fresh()->confirmed_at);
        $this->assertSame($owner->id, $first->fresh()->confirmed_by);
        $this->assertFalse((bool) $first->fresh()->priority_overridden);
    }

    public function test_owner_can_override_priority_when_reason_is_recorded(): void
    {
        [$owner, $salon, $barber, $service, $date] = $this->makeBookingContext();
        $firstCustomer = $this->makeCustomer('first');
        $secondCustomer = $this->makeCustomer('second');
        $bookingService = app(BookingService::class);
        $confirmationService = app(BookingConfirmationService::class);

        Carbon::setTestNow($date->copy()->setTime(8, 0));
        $first = $bookingService->create($firstCustomer, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow($date->copy()->setTime(8, 5));
        $second = $bookingService->create($secondCustomer, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow();

        $confirmationService->confirm(
            $second,
            $owner,
            true,
            'هماهنگی مستقیم با مشتری'
        );

        $second = $second->fresh();
        $first = $first->fresh();

        $this->assertSame(BookingStatus::CONFIRMED, $second->status);
        $this->assertTrue($second->priority_overridden);
        $this->assertSame('هماهنگی مستقیم با مشتری', $second->priority_override_reason);
        $this->assertSame(BookingStatus::CANCELLED, $first->status);
    }

    public function test_manual_confirmation_is_immediate_and_resolves_pending_requests(): void
    {
        [$owner, $salon, $barber, $service, $date] = $this->makeBookingContext();
        $customer = $this->makeCustomer('manual');
        $manualCustomer = $this->makeCustomer('pending');
        $bookingService = app(BookingService::class);
        $confirmationService = app(BookingConfirmationService::class);

        Carbon::setTestNow($date->copy()->setTime(8, 0));
        $pending = $bookingService->create($manualCustomer, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
        ]);

        Carbon::setTestNow($date->copy()->setTime(8, 10));
        $manual = $bookingService->createManual($owner, [
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
            'customer' => $customer,
        ]);

        $confirmationService->reconcileManualConfirmation($manual, $owner);
        Carbon::setTestNow();

        $this->assertSame(BookingStatus::CONFIRMED, $manual->fresh()->status);
        $this->assertNotNull($manual->fresh()->confirmed_at);
        $this->assertSame($owner->id, $manual->fresh()->confirmed_by);
        $this->assertSame(BookingStatus::CANCELLED, $pending->fresh()->status);
    }

    private function makeBookingContext(): array
    {
        $owner = User::query()->create([
            'name' => 'Salon Owner',
            'email' => uniqid('owner-', true) . '@example.test',
            'password' => 'password',
            'role' => UserRole::SALON_OWNER,
        ]);

        $salon = Salon::query()->create([
            'name' => 'Test Salon',
            'slug' => uniqid('test-salon-', true),
            'code' => uniqid('S-', true),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $barber = Barber::query()->create([
            'salon_id' => $salon->id,
            'name' => 'Test Barber',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'salon_id' => $salon->id,
            'name' => 'Test Service',
            'duration_minutes' => 30,
            'price' => 100_000,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $date = Carbon::create(2030, 1, 7)->startOfDay();

        WorkingHour::query()->create([
            'salon_id' => $salon->id,
            'day_of_week' => ($date->dayOfWeek + 1) % 7,
            'start_time' => '09:00:00',
            'end_time' => '22:00:00',
            'is_closed' => false,
            'sort_order' => 0,
        ]);

        return [$owner, $salon, $barber, $service, $date];
    }

    private function makeCustomer(string $suffix): User
    {
        return User::query()->create([
            'name' => 'Customer ' . $suffix,
            'email' => uniqid('customer-' . $suffix . '-', true) . '@example.test',
            'password' => 'password',
            'role' => UserRole::CUSTOMER,
        ]);
    }
}
