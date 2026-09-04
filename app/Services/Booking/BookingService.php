<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability
    ) {
    }


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

                $salon =
                    Salon::query()
                        ->whereKey(
                            $data['salon_id']
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


                $barber =
                    Barber::query()
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


                $service =
                    Service::query()
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


                if (
                    !$customer->exists ||
                    !$customer->isCustomer()
                ) {

                    throw ValidationException::withMessages([
                        'customer_id' =>
                            'حساب مشتری معتبر نیست.',
                    ]);
                }


                $date =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $data['booking_date']
                    );


                if (
                    $date->isBefore(today())
                ) {

                    throw ValidationException::withMessages([
                        'booking_date' =>
                            'امکان رزرو برای تاریخ گذشته وجود ندارد.',
                    ]);
                }


                $slots =
                    $this->availability->slots(
                        $salon,
                        $barber,
                        $service,
                        $date
                    );


                $selected =
                    collect($slots)
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


                $booking =
                    Booking::create([
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


                $booking->load([
                    'salon.owner',
                    'barber',
                    'service',
                    'customer',
                ]);


                if ($booking->customer) {

                    $booking->customer->notify(
                        new BookingNotification(
                            $booking,
                            'created'
                        )
                    );
                }


                if (
                    $booking->salon?->owner &&
                    $status === BookingStatus::PENDING
                ) {

                    $booking
                        ->salon
                        ->owner
                        ->notify(
                            new BookingNotification(
                                $booking,
                                'created'
                            )
                        );
                }


                return $booking;
            }
        );
    }


    public function changeStatus(
        Booking $booking,
        BookingStatus $status
    ): Booking {

        $allowed =
            match ($booking->status) {

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


        if (
            !in_array(
                $status,
                $allowed,
                true
            )
        ) {

            throw ValidationException::withMessages([
                'status' =>
                    'تغییر وضعیت این نوبت مجاز نیست.',
            ]);
        }


        $booking->update([
            'status' =>
                $status,
        ]);


        $booking->load([
            'salon',
            'barber',
            'service',
            'customer',
        ]);


        if ($booking->customer) {

            $booking->customer->notify(
                new BookingNotification(
                    $booking,
                    'status_changed'
                )
            );
        }


        return $booking;
    }
}
