<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability
    ) {
    }

    /**
     * Customer booking.
     *
     * Default status = PENDING
     */
    public function create(
        User $customer,
        array $data,
        BookingStatus $status = BookingStatus::PENDING
    ): Booking {
        return DB::transaction(
            function () use (
                $customer,
                $data,
                $status
            ) {
                return $this->createBooking(
                    $customer,
                    $data,
                    $status,
                    false
                );
            }
        );
    }

    /**
     * Manual booking created by salon owner.
     *
     * IMPORTANT:
     * Manual bookings are immediately CONFIRMED.
     * No approval step exists.
     */
    public function createManual(
        User $owner,
        array $data
    ): Booking {
        return DB::transaction(
            function () use (
                $owner,
                $data
            ) {
                $salon = Salon::query()
                    ->whereKey($data['salon_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$salon) {
                    throw ValidationException::withMessages([
                        'salon_id' =>
                            'سالن انتخاب شده در دسترس نیست.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Make sure this user actually owns this salon
                |--------------------------------------------------------------------------
                */

                $isOwner = $salon
                    ->owner()
                    ->whereKey($owner->id)
                    ->exists();

                if (!$isOwner) {
                    throw ValidationException::withMessages([
                        'salon_id' =>
                            'شما اجازه ثبت نوبت برای این سالن را ندارید.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Manual booking = immediately confirmed
                |--------------------------------------------------------------------------
                */

                $customer = $data['customer'] ?? null;

                if (!$customer && !empty($data['new_customer_phone'])) {
                    $phone = PhoneNumber::normalize(
                        (string) $data['new_customer_phone']
                    );

                    $customer = User::query()
                        ->where('phone', $phone)
                        ->lockForUpdate()
                        ->first();

                    if ($customer && !$customer->isCustomer()) {
                        throw ValidationException::withMessages([
                            'new_customer_phone' =>
                                'این شماره موبایل برای حساب دیگری ثبت شده است.',
                        ]);
                    }

                    if (!$customer) {
                        $customer = User::create([
                            'name' => trim(
                                (string) ($data['new_customer_name'] ?? '')
                            ),
                            'phone' => $phone,
                            'phone_verified_at' => null,
                            'email' => null,
                            'password' => null,
                            'role' => \App\Enums\UserRole::CUSTOMER,
                            'must_change_password' => false,
                        ]);
                    }
                }

                return $this->createBooking(
                    $customer,
                    $data,
                    BookingStatus::CONFIRMED,
                    true,
                    $owner
                );
            }
        );
    }

    /**
     * Shared internal booking creation logic.
     */
    private function createBooking(
        ?User $customer,
        array $data,
        BookingStatus $status,
        bool $manual = false,
        ?User $manualOwner = null
    ): Booking {
        /*
        |--------------------------------------------------------------------------
        | Salon
        |--------------------------------------------------------------------------
        */

        $salon = Salon::query()
            ->whereKey($data['salon_id'])
            ->where('is_active', true)
            ->first();

        if (!$salon) {
            throw ValidationException::withMessages([
                'salon_id' =>
                    'سالن انتخاب شده در دسترس نیست.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Manual owner authorization
        |--------------------------------------------------------------------------
        */

        if ($manual) {
            if (!$manualOwner) {
                throw ValidationException::withMessages([
                    'owner' =>
                        'صاحب سالن معتبر نیست.',
                ]);
            }

            $isOwner = $salon
                ->owner()
                ->whereKey($manualOwner->id)
                ->exists();

            if (!$isOwner) {
                throw ValidationException::withMessages([
                    'owner' =>
                        'شما اجازه ثبت نوبت برای این سالن را ندارید.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Customer validation
        |--------------------------------------------------------------------------
        */

        if (!$customer) {
            throw ValidationException::withMessages([
                'customer_id' =>
                    'مشتری برای ثبت نوبت الزامی است.',
            ]);
        }

        if (
            !$customer->exists ||
            !$customer->isCustomer()
        ) {
            throw ValidationException::withMessages([
                'customer_id' =>
                    'حساب مشتری معتبر نیست.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Barber
        |--------------------------------------------------------------------------
        |
        | lockForUpdate() helps serialize competing bookings
        | for the same barber.
        |
        */

        $barber = Barber::query()
            ->whereKey($data['barber_id'])
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (!$barber) {
            throw ValidationException::withMessages([
                'barber_id' =>
                    'آرایشگر انتخاب شده در این سالن در دسترس نیست.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Service
        |--------------------------------------------------------------------------
        */

        $service = Service::query()
            ->whereKey($data['service_id'])
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->first();

        if (!$service) {
            throw ValidationException::withMessages([
                'service_id' =>
                    'خدمت انتخاب شده در این سالن در دسترس نیست.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        $timezone = config('app.timezone', 'Asia/Tehran');

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $data['booking_date'],
            $timezone
        )->startOfDay();

        if ($date->lt(now($timezone)->startOfDay())) {
            throw ValidationException::withMessages([
                'booking_date' =>
                    'امکان رزرو برای تاریخ گذشته وجود ندارد.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Availability
        |--------------------------------------------------------------------------
        |
        | Both customer and manual bookings use exactly
        | the same availability engine.
        |
        */

        $slots = $this->availability->slots(
            $salon,
            $barber,
            $service,
            $date
        );

        $selected = collect($slots)
            ->firstWhere(
                'start',
                $data['start_time']
            );

        if (
            !$selected ||
            !($selected['available'] ?? false)
        ) {
            throw ValidationException::withMessages([
                'start_time' =>
                    'این زمان دیگر در دسترس نیست.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create booking
        |--------------------------------------------------------------------------
        */

        $booking = Booking::create([
            'salon_id' =>
                $salon->id,

            'barber_id' =>
                $barber->id,

            'service_id' =>
                $service->id,

            'customer_id' =>
                $customer->id,

            'booking_date' =>
                $date->toDateString(),

            'start_time' =>
                $selected['start'],

            'end_time' =>
                $selected['end'],

            'price' =>
                $service->price,

            'status' =>
                $status,

            'notes' =>
                $data['notes'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load relationships
        |--------------------------------------------------------------------------
        */

        $booking->load([
            'salon.owner',
            'barber',
            'service',
            'customer',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Post-commit notification pipeline
        |--------------------------------------------------------------------------
        |
        | BookingCreated implements ShouldDispatchAfterCommit, so the
        | notification pipeline cannot run before the booking transaction
        | has actually committed.
        |
        */

        BookingCreated::dispatch($booking);

        return $booking;
    }

    /**
     * Change booking status atomically.
     */
    public function changeStatus(
        Booking $booking,
        BookingStatus $status
    ): Booking {
        return DB::transaction(function () use (
            $booking,
            $status
        ): Booking {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->with([
                    'salon',
                    'barber',
                    'service',
                    'customer',
                ])
                ->findOrFail($booking->id);

            $allowed = match ($lockedBooking->status) {
                BookingStatus::PENDING => [
                    BookingStatus::CONFIRMED,
                    BookingStatus::CANCELLED,
                ],

                BookingStatus::CONFIRMED => [
                    BookingStatus::COMPLETED,
                    BookingStatus::CANCELLED,
                ],

                BookingStatus::COMPLETED,
                BookingStatus::CANCELLED => [],
            };

            if (!in_array($status, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' =>
                        'تغییر وضعیت این نوبت مجاز نیست.',
                ]);
            }

            $from = $lockedBooking->status;

            $lockedBooking->update([
                'status' => $status,
            ]);

            $lockedBooking->refresh();

            $lockedBooking->load([
                'salon',
                'barber',
                'service',
                'customer',
            ]);

            BookingStatusChanged::dispatch(
                $lockedBooking,
                $from,
                $status
            );

            return $lockedBooking;
        });
    }

    /**
     * Customer updates a pending booking.
     */
    public function updateByCustomer(
        User $customer,
        Booking $booking,
        array $data
    ): Booking {
        return DB::transaction(
            function () use (
                $customer,
                $booking,
                $data
            ) {
                $booking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $booking->id
                    );

                if (
                    (int) $booking->customer_id !==
                    (int) $customer->id
                ) {
                    abort(403);
                }

                if (
                    $booking->status !==
                    BookingStatus::PENDING
                ) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'فقط نوبت‌های در انتظار امکان ویرایش دارند.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Salon cannot change
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $data['salon_id'] !==
                    (int) $booking->salon_id
                ) {
                    throw ValidationException::withMessages([
                        'salon_id' =>
                            'امکان تغییر سالن این نوبت وجود ندارد.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Salon
                |--------------------------------------------------------------------------
                */

                $salon = Salon::query()
                    ->whereKey(
                        $booking->salon_id
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if (!$salon) {
                    throw ValidationException::withMessages([
                        'salon_id' =>
                            'سالن انتخاب شده در دسترس نیست.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Barber
                |--------------------------------------------------------------------------
                */

                $barber = Barber::query()
                    ->whereKey(
                        $data['barber_id']
                    )
                    ->where(
                        'salon_id',
                        $salon->id
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$barber) {
                    throw ValidationException::withMessages([
                        'barber_id' =>
                            'آرایشگر انتخاب شده در این سالن در دسترس نیست.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Service
                |--------------------------------------------------------------------------
                */

                $service = Service::query()
                    ->whereKey(
                        $data['service_id']
                    )
                    ->where(
                        'salon_id',
                        $salon->id
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if (!$service) {
                    throw ValidationException::withMessages([
                        'service_id' =>
                            'خدمت انتخاب شده در این سالن در دسترس نیست.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Date
                |--------------------------------------------------------------------------
                */

                $date = Carbon::createFromFormat(
                    'Y-m-d',
                    $data['booking_date']
                )->startOfDay();

                if ($date->isBefore(today())) {
                    throw ValidationException::withMessages([
                        'booking_date' =>
                            'امکان انتخاب تاریخ گذشته وجود ندارد.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                $slots = $this->availability->slots(
                    $salon,
                    $barber,
                    $service,
                    $date,
                    $booking->id
                );

                $selected = collect($slots)
                    ->firstWhere(
                        'start',
                        $data['start_time']
                    );

                if (
                    !$selected ||
                    !($selected['available'] ?? false)
                ) {
                    throw ValidationException::withMessages([
                        'start_time' =>
                            'این زمان دیگر در دسترس نیست.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Update
                |--------------------------------------------------------------------------
                */

                $booking->update([
                    'barber_id' =>
                        $barber->id,

                    'service_id' =>
                        $service->id,

                    'booking_date' =>
                        $date->toDateString(),

                    'start_time' =>
                        $selected['start'],

                    'end_time' =>
                        $selected['end'],

                    'price' =>
                        $service->price,

                    'notes' =>
                        $data['notes'] ?? null,
                ]);

                return $booking->fresh([
                    'salon',
                    'barber',
                    'service',
                    'customer',
                ]);
            }
        );
    }
}
