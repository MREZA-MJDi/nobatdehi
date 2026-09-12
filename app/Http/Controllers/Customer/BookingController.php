<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingAvailabilityRequest;
use App\Http\Requests\Customer\BookingPrepareRequest;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly AvailabilityService $availabilityService
    ) {
    }

    /**
     * Return real availability for the selected
     * barber, service and date.
     *
     * Used by Calendar + Time Picker.
     */
    public function availability(
        BookingAvailabilityRequest $request
    ): JsonResponse {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Salon
        |--------------------------------------------------------------------------
        |
        | We resolve the salon from the selected barber/service
        | instead of trusting a separate salon_id from the client.
        |
        */

        $barber = Barber::query()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->first();

        if (!$barber) {
            throw ValidationException::withMessages([
                'barber_id' =>
                    'آرایشگر انتخاب شده در دسترس نیست.',
            ]);
        }

        $salon = Salon::query()
            ->whereKey($barber->salon_id)
            ->where('is_active', true)
            ->first();

        if (!$salon) {
            throw ValidationException::withMessages([
                'barber_id' =>
                    'سالن انتخاب شده در دسترس نیست.',
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
                    'خدمت انتخاب شده در این سالن معتبر نیست.',
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

        /*
        |--------------------------------------------------------------------------
        | Availability
        |--------------------------------------------------------------------------
        */

        $slots = $this->availabilityService->slots(
            $salon,
            $barber,
            $service,
            $date
        );

        $availableSlots = collect($slots)
            ->where(
                'available',
                true
            )
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'date' =>
                $date->toDateString(),

            'salon' => [
                'id' =>
                    $salon->id,

                'name' =>
                    $salon->name,
            ],

            'barber' => [
                'id' =>
                    $barber->id,
            ],

            'service' => [
                'id' =>
                    $service->id,

                'name' =>
                    $service->name,

                'duration_minutes' =>
                    (int) $service->duration_minutes,

                'price' =>
                    $service->price,
            ],

            /*
            |--------------------------------------------------------------------------
            | All generated slots
            |--------------------------------------------------------------------------
            */

            'slots' =>
                $slots,

            /*
            |--------------------------------------------------------------------------
            | Only actually available slots
            |--------------------------------------------------------------------------
            */

            'available_slots' =>
                $availableSlots,

            'has_availability' =>
                !empty($availableSlots),
        ]);
    }

    /**
     * Create customer booking.
     *
     * Customer booking starts as PENDING.
     */
    public function prepare(
        BookingPrepareRequest $request
    ): JsonResponse {
        $booking = $this->bookingService->create(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,

            'message' =>
                'درخواست نوبت با موفقیت ثبت شد و برای تأیید به سالن ارسال شد.',

            'booking' => [
                'id' =>
                    $booking->id,

                'booking_date' =>
                    $booking->booking_date,

                'start_time' =>
                    $booking->start_time,

                'end_time' =>
                    $booking->end_time,

                'status' =>
                    $booking->status->value,

                'status_label' =>
                    $booking->status->label(),

                'price' =>
                    $booking->price,

                'notes' =>
                    $booking->notes,

                'salon' =>
                    $booking->salon
                        ? [
                        'id' =>
                            $booking->salon->id,

                        'name' =>
                            $booking->salon->name,
                    ]
                        : null,

                'barber' =>
                    $booking->barber
                        ? [
                        'id' =>
                            $booking->barber->id,

                        'name' =>
                            $booking->barber->name
                            ?? $booking->barber->user?->name,
                    ]
                        : null,

                'service' =>
                    $booking->service
                        ? [
                        'id' =>
                            $booking->service->id,

                        'name' =>
                            $booking->service->name,

                        'duration_minutes' =>
                            (int) $booking
                                ->service
                                ->duration_minutes,
                    ]
                        : null,
            ],
        ], 201);
    }
}
