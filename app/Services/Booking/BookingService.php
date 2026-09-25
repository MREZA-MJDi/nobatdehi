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
     * Maximum number of simultaneous pending bookings per customer.
     *
     * The limit is global across salons. Confirmed, completed and cancelled
     * bookings do not consume this capacity.
     */
    public const MAX_PENDING_BOOKINGS = 2;

    public function pendingBookingCount(User $customer): int
    {
        return $customer
            ->bookings()
            ->where('status', BookingStatus::PENDING)
            ->count();
    }

    public function remainingPendingBookingSlots(User $customer): int
    {
        return max(
            0,
            self::MAX_PENDING_BOOKINGS - $this->pendingBookingCount($customer)
        );
    }

    public function assertCustomerCanCreatePendingBooking(User $customer): void
    {
        if ($this->pendingBookingCount($customer) >= self::MAX_PENDING_BOOKINGS) {
            throw ValidationException::withMessages([
                'pending_bookings' =>
                    'در حال حاضر حداکثر ۲ نوبت در انتظار تأیید می‌توانی داشته باشی. بعد از تأیید، تکمیل یا لغو یکی از نوبت‌ها، دوباره می‌توانی نوبت جدید بگیری.',
            ]);
        }
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
                /*
                |--------------------------------------------------------------------------
                | Customer pending limit
                |--------------------------------------------------------------------------
                |
                | This is intentionally enforced inside the transaction so the
                | rule cannot be bypassed by manipulating the frontend or by
                | submitting two booking requests at nearly the same time.
                */

                if ($status === BookingStatus::PENDING) {
                    $lockedCustomer = User::query()
                        ->lockForUpdate()
                        ->find($customer->id);

                    if (!$lockedCustomer || !$lockedCustomer->isCustomer()) {
                        throw ValidationException::withMessages([
                            'customer_id' =>
                                'حساب مشتری معتبر نیست.',
                        ]);
                    }

                    $this->assertCustomerCanCreatePendingBooking(
                        $lockedCustomer
                    );

                    $customer = $lockedCustomer;
                }

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
                | Manual booking = confirmed immediately, no customer account.
                |--------------------------------------------------------------------------
                |
                | The salon only records a contact snapshot on the booking.
                | No User is selected, created, authenticated or modified here.
                */

                return $this->createBooking(
                    null,
                    $data,
                    BookingStatus::CONFIRMED,
                    true,
                    $owner,
                    trim((string) ($data['customer_name'] ?? '')),
                    PhoneNumber::normalize((string) ($data['customer_phone'] ?? ''))
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
        ?User $manualOwner = null,
        ?string $manualCustomerName = null,
        ?string $manualCustomerPhone = null
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
        | Customer / manual snapshot validation
        |--------------------------------------------------------------------------
        */

        if ($manual) {
            if (!$manualCustomerName || !$manualCustomerPhone) {
                throw ValidationException::withMessages([
                    'customer_name' =>
                        'نام و شماره موبایل مشتری برای نوبت دستی الزامی است.',
                ]);
            }
        } elseif (!$customer) {
            throw ValidationException::withMessages([
                'customer_id' =>
                    'مشتری برای ثبت نوبت الزامی است.',
            ]);
        } elseif (
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
                $customer?->id,

            'customer_name' =>
                $manual
                    ? $manualCustomerName
                    : ($customer?->name ?? null),

            'customer_phone' =>
                $manual
                    ? $manualCustomerPhone
                    : ($customer?->phone ?? null),

            'is_manual' =>
                $manual,

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

            if ($status === BookingStatus::CONFIRMED) {
                $this->assertConfirmedTimeIsAvailable($lockedBooking);
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
     * Confirmed bookings are the only final time owners.
     *
     * Multiple pending requests may coexist on the same time. Once one of
     * them is confirmed, a later confirmation must not create an overlap.
     */
    private function assertConfirmedTimeIsAvailable(Booking $booking): void
    {
        $bookingStart = Carbon::parse(
            $booking->start_time
        )->format('H:i:s');

        $bookingEnd = Carbon::parse(
            $booking->end_time
        )->format('H:i:s');

        $conflict = Booking::query()
            ->where('barber_id', $booking->barber_id)
            ->whereDate('booking_date', $booking->booking_date)
            ->where('status', BookingStatus::CONFIRMED->value)
            ->whereKeyNot($booking->id)
            ->where(function ($query) use ($bookingStart, $bookingEnd) {
                $query
                    ->where('start_time', '<', $bookingEnd)
                    ->where('end_time', '>', $bookingStart);
            })
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'status' =>
                    'این نوبت با یک نوبت تأییدشده تداخل دارد. اولویت با نوبتی است که زودتر توسط سالن تأیید شده است.',
            ]);
        }
    }

    /**
     * Customer can cancel only while the booking is still pending.
     *
     * The booking row is locked before checking the status so an approval
     * happening concurrently cannot be followed by an unintended customer
     * cancellation.
     */
    public function cancelByCustomer(
        User $customer,
        Booking $booking
    ): Booking {
        return DB::transaction(
            function () use ($customer, $booking) {
                $lockedBooking = Booking::query()
                    ->lockForUpdate()
                    ->with([
                        'salon',
                        'barber',
                        'service',
                        'customer',
                    ])
                    ->find($booking->id);

                if (!$lockedBooking) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'نوبت موردنظر دیگر وجود ندارد.',
                    ]);
                }

                if (
                    (int) $lockedBooking->customer_id !==
                    (int) $customer->id
                ) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'شما اجازه لغو این نوبت را ندارید.',
                    ]);
                }

                if (
                    $lockedBooking->status !==
                    BookingStatus::PENDING
                ) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'این نوبت دیگر در وضعیت «در انتظار» نیست و قابل لغو نیست.',
                    ]);
                }

                $from = $lockedBooking->status;

                $lockedBooking->update([
                    'status' => BookingStatus::CANCELLED,
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
                    BookingStatus::CANCELLED
                );

                return $lockedBooking;
            }
        );
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
                    throw ValidationException::withMessages([
                        'booking' =>
                            'شما اجازه ویرایش این نوبت را ندارید.',
                    ]);
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
                    $data['booking_date'],
                    config('app.timezone', 'Asia/Tehran')
                )->startOfDay();

                if (
                    $date->lt(
                        now(config('app.timezone', 'Asia/Tehran'))->startOfDay()
                    )
                ) {
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

                $updatedBooking = $booking->fresh([
                    'salon',
                    'barber',
                    'service',
                    'customer',
                ]);

                \App\Events\BookingUpdated::dispatch($updatedBooking);

                return $updatedBooking;
            }
        );
    }
}
