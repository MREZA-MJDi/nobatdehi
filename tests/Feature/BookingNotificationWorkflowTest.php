<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Jobs\SendBookingSms;
use App\Listeners\HandleBookingCreated;
use App\Listeners\HandleBookingStatusChanged;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Notifications\BookingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingNotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_created_listener_notifies_customer_and_owner_and_queues_approver_sms(): void
    {
        Notification::fake();
        Queue::fake();

        [$owner, $customer, $booking] = $this->booking();

        (new HandleBookingCreated())->handle(
            new BookingCreated($booking)
        );

        Notification::assertSentTo(
            $customer,
            BookingNotification::class,
            function (BookingNotification $notification) {
                return $notification->event === 'created';
            }
        );

        Notification::assertSentTo(
            $owner,
            BookingNotification::class,
            function (BookingNotification $notification) {
                return $notification->event === 'created';
            }
        );

        Queue::assertPushed(
            SendBookingSms::class,
            function (SendBookingSms $job) use ($booking) {
                return $job->bookingId === $booking->id
                    && $job->recipientType === 'approver';
            }
        );
    }

    public function test_status_listener_notifies_customer_and_only_queues_customer_sms_when_enabled(): void
    {
        Notification::fake();
        Queue::fake();

        config([
            'services.nobat_sms.customer_status_sms' => true,
        ]);

        [$owner, $customer, $booking] = $this->booking();

        $booking->update([
            'status' => BookingStatus::CONFIRMED,
        ]);

        (new HandleBookingStatusChanged())->handle(
            new BookingStatusChanged(
                $booking->fresh(),
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED
            )
        );

        Notification::assertSentTo(
            $customer,
            BookingNotification::class,
            function (BookingNotification $notification) {
                return $notification->event === 'status_changed';
            }
        );

        Queue::assertPushed(
            SendBookingSms::class,
            function (SendBookingSms $job) use ($booking) {
                return $job->bookingId === $booking->id
                    && $job->recipientType === 'customer';
            }
        );
    }

    private function booking(): array
    {
        $owner = User::create([
            'name' => 'Owner',
            'phone' => '09120002001',
            'email' => 'owner' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'salon_owner',
        ]);

        $customer = User::create([
            'name' => 'Customer',
            'phone' => '09120002002',
            'email' => 'customer' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $salon = Salon::create([
            'name' => 'Notification Salon',
            'slug' => 'notification-salon-' . uniqid(),
            'code' => 'NOT-' . random_int(1000, 9999),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $barber = Barber::create([
            'salon_id' => $salon->id,
            'name' => 'Notification Barber',
            'phone' => '09120002003',
            'is_active' => true,
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Service',
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

        $booking = Booking::create([
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

        return [$owner, $customer, $booking];
    }
}
