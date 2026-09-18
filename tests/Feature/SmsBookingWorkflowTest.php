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
use App\Services\Booking\SmsBookingReplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SmsBookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_one_confirms_a_pending_booking_for_authorized_barber(): void
    {
        Event::fake();

        [$booking, $barber] = $this->pendingBooking();

        $result = app(SmsBookingReplyService::class)->handle(
            $barber->phone,
            '۱',
            'provider-1',
            ['id' => 'provider-1']
        );

        $this->assertTrue($result['ok']);
        $this->assertSame('processed', $result['status']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::CONFIRMED->value,
        ]);

        $this->assertDatabaseHas('sms_inbound_messages', [
            'provider_message_id' => 'provider-1',
            'booking_id' => $booking->id,
            'status' => 'processed',
            'action' => 'confirm',
        ]);
    }

    public function test_sms_two_cancels_a_pending_booking_for_authorized_owner(): void
    {
        Event::fake();

        [$booking, $barber, $owner] = $this->pendingBooking(true);

        $result = app(SmsBookingReplyService::class)->handle(
            $owner->phone,
            '2',
            'provider-2',
            ['id' => 'provider-2']
        );

        $this->assertTrue($result['ok']);
        $this->assertSame('processed', $result['status']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::CANCELLED->value,
        ]);
    }

    public function test_ambiguous_one_requires_booking_code(): void
    {
        Event::fake();

        [$bookingOne, $bookingTwo, $barber] =
            $this->twoPendingBookingsForSameBarber();

        $result = app(SmsBookingReplyService::class)->handle(
            $barber->phone,
            '1',
            'provider-3',
            ['id' => 'provider-3']
        );

        $this->assertFalse($result['ok']);
        $this->assertSame('booking_not_found_or_ambiguous', $result['reason']);

        $result = app(SmsBookingReplyService::class)->handle(
            $barber->phone,
            '1-' . $bookingOne->id,
            'provider-4',
            ['id' => 'provider-4']
        );

        $this->assertTrue($result['ok']);
        $this->assertSame('processed', $result['status']);

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingOne->id,
            'status' => BookingStatus::CONFIRMED->value,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingTwo->id,
            'status' => BookingStatus::PENDING->value,
        ]);
    }

    public function test_repeating_same_provider_message_is_idempotent(): void
    {
        Event::fake();

        [$booking, $barber] = $this->pendingBooking();

        $service = app(SmsBookingReplyService::class);

        $first = $service->handle(
            $barber->phone,
            '1',
            'provider-5',
            ['id' => 'provider-5']
        );

        $second = $service->handle(
            $barber->phone,
            '1',
            'provider-5',
            ['id' => 'provider-5']
        );

        $this->assertTrue($first['ok']);
        $this->assertTrue($second['duplicate']);
    }

    private function pendingBooking(bool $withOwner = false): array
    {
        $owner = User::create([
            'name' => 'Owner',
            'phone' => '09120001001',
            'email' => 'owner' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'salon_owner',
        ]);

        $customer = User::create([
            'name' => 'Customer',
            'phone' => '09120001002',
            'email' => 'customer' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $salon = Salon::create([
            'name' => 'SMS Salon',
            'slug' => 'sms-salon-' . uniqid(),
            'code' => 'SMS-' . random_int(1000, 9999),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $barber = Barber::create([
            'salon_id' => $salon->id,
            'name' => 'SMS Barber',
            'phone' => '09120001003',
            'is_active' => true,
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Service',
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

        $booking = Booking::create([
            'salon_id' => $salon->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'price' => 120000,
            'status' => BookingStatus::PENDING,
        ]);

        return $withOwner
            ? [$booking, $barber, $owner]
            : [$booking, $barber];
    }

    private function twoPendingBookingsForSameBarber(): array
    {
        [$bookingOne, $barber] = $this->pendingBooking();
        $bookingTwo = $bookingOne->replicate();
        $bookingTwo->customer_id = $bookingOne->customer_id;
        $bookingTwo->start_time = '11:00';
        $bookingTwo->end_time = '12:00';
        $bookingTwo->save();

        return [$bookingOne, $bookingTwo, $barber];
    }
}
