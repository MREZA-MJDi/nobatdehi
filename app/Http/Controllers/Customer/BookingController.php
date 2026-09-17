<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingAvailabilityRequest;
use App\Http\Requests\Customer\BookingPrepareRequest;
use App\Http\Requests\Customer\BookingRequest;
use App\Models\Booking;
use App\Models\Salon;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CREATE / PUBLIC BOOKING PAGE
    |--------------------------------------------------------------------------
    */

    public function create(
        Salon $salon
    ): View {
        abort_unless(
            $salon->is_active,
            404
        );

        $barbers = $salon
            ->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'public.booking',
            [
                'salon' => $salon,
                'barbers' => $barbers,
                'services' => $services,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public function availability(
        BookingAvailabilityRequest $request,
        Salon $salon,
        AvailabilityService $availability
    ): JsonResponse {
        abort_unless(
            $salon->is_active,
            404
        );

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Make sure barber belongs to this salon
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Make sure service belongs to this salon
        |--------------------------------------------------------------------------
        */

        $service = $salon
            ->services()
            ->whereKey($data['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Parse date
        |--------------------------------------------------------------------------
        */

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $data['booking_date']
        );

        /*
        |--------------------------------------------------------------------------
        | Get available slots
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'ok' => true,

            'slots' => $availability->slots(
                $salon,
                $barber,
                $service,
                $date
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PREPARE BOOKING
    |--------------------------------------------------------------------------
    |
    | This method stores the temporary booking data in session.
    |
    | Important:
    | - Normal browser request => redirect
    | - AJAX/JSON request     => JSON + redirect URL
    |
    */

    public function prepare(
        BookingPrepareRequest $request,
        Salon $salon
    ): JsonResponse|RedirectResponse {
        abort_unless(
            $salon->is_active,
            404
        );

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Prevent booking another salon by manipulating salon_id
        |--------------------------------------------------------------------------
        */

        if (
            (int) $data['salon_id'] !==
            (int) $salon->id
        ) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify barber belongs to salon
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Verify service belongs to salon
        |--------------------------------------------------------------------------
        */

        $service = $salon
            ->services()
            ->whereKey($data['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Store normalized booking payload
        |--------------------------------------------------------------------------
        */

        $pending = [
            'salon_id' => (int) $salon->id,

            'barber_id' => (int) $barber->id,

            'service_id' => (int) $service->id,

            'booking_date' => $data['booking_date'],

            'start_time' => $data['start_time'],

            'notes' => $data['notes'] ?? null,
        ];

        $request
            ->session()
            ->put(
                'booking.pending',
                $pending
            );

        /*
        |--------------------------------------------------------------------------
        | Guest user
        |--------------------------------------------------------------------------
        */

        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,

                    'requires_auth' => true,

                    'redirect' => route('login'),

                    'message' =>
                        'برای ثبت نهایی نوبت ابتدا وارد حساب خود شوید.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'برای ثبت نهایی نوبت وارد حساب خود شوید.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Only customers can continue booking
        |--------------------------------------------------------------------------
        */

        if (!$request->user()->isCustomer()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,

                    'message' =>
                        'این حساب امکان رزرو مشتری را ندارد.',
                ], 403);
            }

            return redirect()
                ->route('brand.intro')
                ->with(
                    'error',
                    'این حساب امکان رزرو مشتری را ندارد.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | AJAX / JSON response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,

                'redirect' => route(
                    'customer.bookings.confirm'
                ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normal request response
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'customer.bookings.confirm'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM PAGE
    |--------------------------------------------------------------------------
    */

    public function confirm(
        Request $request
    ): View|RedirectResponse {
        $pending = $request
            ->session()
            ->get(
                'booking.pending'
            );

        /*
        |--------------------------------------------------------------------------
        | No pending booking
        |--------------------------------------------------------------------------
        */

        if (!is_array($pending)) {
            return redirect()
                ->route('salons.discover')
                ->with(
                    'error',
                    'اطلاعات رزرو پیدا نشد. دوباره انتخاب کنید.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate salon
        |--------------------------------------------------------------------------
        */

        $salon = Salon::query()
            ->whereKey(
                $pending['salon_id'] ?? null
            )
            ->where('is_active', true)
            ->first();

        if (!$salon) {
            $request
                ->session()
                ->forget(
                    'booking.pending'
                );

            return redirect()
                ->route('salons.discover')
                ->with(
                    'error',
                    'سالن موردنظر دیگر در دسترس نیست.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate barber
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->whereKey(
                $pending['barber_id'] ?? null
            )
            ->where('is_active', true)
            ->first();

        if (!$barber) {
            $request
                ->session()
                ->forget(
                    'booking.pending'
                );

            return redirect()
                ->route(
                    'public.salons.show',
                    $salon
                )
                ->with(
                    'error',
                    'آرایشگر انتخاب‌شده دیگر در دسترس نیست.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate service
        |--------------------------------------------------------------------------
        */

        $service = $salon
            ->services()
            ->whereKey(
                $pending['service_id'] ?? null
            )
            ->where('is_active', true)
            ->first();

        if (!$service) {
            $request
                ->session()
                ->forget(
                    'booking.pending'
                );

            return redirect()
                ->route(
                    'public.salons.show',
                    $salon
                )
                ->with(
                    'error',
                    'خدمت انتخاب‌شده دیگر در دسترس نیست.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Return confirmation page
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.bookings.confirm',
            [
                'salon' => $salon,
                'barber' => $barber,
                'service' => $service,
                'pending' => $pending,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE BOOKING
    |--------------------------------------------------------------------------
    */

    public function store(
        BookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Pending booking from session
        |--------------------------------------------------------------------------
        */

        $pending = $request
            ->session()
            ->get(
                'booking.pending'
            );

        if (!is_array($pending)) {
            return redirect()
                ->route('salons.discover')
                ->with(
                    'error',
                    'اطلاعات رزرو پیدا نشد. دوباره انتخاب کنید.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate submitted form
        |--------------------------------------------------------------------------
        */

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Prevent mismatch between confirm page and submitted data
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'salon_id',
                'barber_id',
                'service_id',
                'booking_date',
                'start_time',
            ] as $field
        ) {
            if (
                (string) ($data[$field] ?? '') !==
                (string) ($pending[$field] ?? '')
            ) {
                return redirect()
                    ->route(
                        'customer.bookings.confirm'
                    )
                    ->withErrors([
                        'booking' =>
                            'اطلاعات رزرو تغییر کرده است. دوباره انتخاب کنید.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create booking
        |--------------------------------------------------------------------------
        */

        $booking = $bookingService->create(
            $request->user(),
            $data
        );

        /*
        |--------------------------------------------------------------------------
        | Clear pending booking
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'booking.pending'
            );

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'customer.dashboard'
            )
            ->with(
                'success',
                'نوبت شما با موفقیت ثبت شد.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT CUSTOMER BOOKING
    |--------------------------------------------------------------------------
    */

    public function edit(
        Booking $booking
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        if (
            (int) $booking->customer_id !==
            (int) auth()->id()
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Only pending bookings can be edited
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $booking->status->value === 'pending',
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Load relations
        |--------------------------------------------------------------------------
        */

        $booking->load([
            'salon',
            'barber',
            'service',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Salon must remain active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $booking->salon?->is_active,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Active barbers
        |--------------------------------------------------------------------------
        */

        $barbers = $booking->salon
            ->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Active services
        |--------------------------------------------------------------------------
        */

        $services = $booking->salon
            ->services()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'customer.bookings.edit',
            [
                'booking' => $booking,
                'salon' => $booking->salon,
                'barbers' => $barbers,
                'services' => $services,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE CUSTOMER BOOKING
    |--------------------------------------------------------------------------
    */

    public function update(
        BookingRequest $request,
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        if (
            (int) $booking->customer_id !==
            (int) $request->user()->id
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending only
        |--------------------------------------------------------------------------
        */

        if (
            $booking->status->value !==
            'pending'
        ) {
            return redirect()
                ->route(
                    'customer.dashboard'
                )
                ->with(
                    'error',
                    'فقط نوبت‌های در انتظار امکان ویرایش دارند.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $bookingService->updateByCustomer(
            $request->user(),
            $booking,
            $request->validated()
        );

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'customer.dashboard'
            )
            ->with(
                'success',
                'نوبت شما با موفقیت ویرایش شد.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL CUSTOMER BOOKING
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        if (
            (int) $booking->customer_id !==
            (int) auth()->id()
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending only
        |--------------------------------------------------------------------------
        */

        if (
            $booking->status->value !==
            'pending'
        ) {
            return redirect()
                ->route(
                    'customer.dashboard'
                )
                ->with(
                    'error',
                    'فقط نوبت‌های در انتظار امکان لغو دارند.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Change status
        |--------------------------------------------------------------------------
        */

        $bookingService->changeStatus(
            $booking,
            BookingStatus::CANCELLED
        );

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'customer.dashboard'
            )
            ->with(
                'success',
                'نوبت شما لغو شد.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT BOOKING AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public function editAvailability(
        BookingAvailabilityRequest $request,
        Booking $booking,
        AvailabilityService $availability
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        if (
            (int) $booking->customer_id !==
            (int) auth()->id()
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending only
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $booking->status->value === 'pending',
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Validate request
        |--------------------------------------------------------------------------
        */

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Salon
        |--------------------------------------------------------------------------
        */

        $salon = $booking->salon;

        abort_unless(
            $salon?->is_active,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Barber
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->whereKey(
                $data['barber_id']
            )
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Service
        |--------------------------------------------------------------------------
        */

        $service = $salon
            ->services()
            ->whereKey(
                $data['service_id']
            )
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $data['booking_date']
        );

        /*
        |--------------------------------------------------------------------------
        | Availability
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'ok' => true,

            'slots' => $availability->slots(
                $salon,
                $barber,
                $service,
                $date,
                $booking->id
            ),
        ]);
    }
}
