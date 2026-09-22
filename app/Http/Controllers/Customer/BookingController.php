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
        | Parse date / schedule
        |--------------------------------------------------------------------------
        */

        $timezone = config('app.timezone', 'Asia/Tehran');

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $data['booking_date'],
            $timezone
        )->startOfDay();

        if ($date->lt(now($timezone)->startOfDay())) {
            return response()->json([
                'ok' => false,
                'message' => 'امکان انتخاب تاریخ گذشته وجود ندارد.',
            ], 422);
        }

        $dayOfWeek = ($date->dayOfWeek + 1) % 7;

        $dayNames = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه',
        ];

        $dayRows = $salon->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        $dailyStatus = $salon->dailyStatuses()
            ->whereDate('date', $date->toDateString())
            ->first();

        $isDailyClosed = (bool) ($dailyStatus?->is_closed);

        $workingHours = $dayRows
            ->filter(fn ($row) =>
                ! $row->is_closed &&
                filled($row->start_time) &&
                filled($row->end_time)
            )
            ->map(fn ($row) => [
                'start' => substr((string) $row->start_time, 0, 5),
                'end' => substr((string) $row->end_time, 0, 5),
            ])
            ->values()
            ->all();

        $isWeeklyClosed =
            $workingHours === [] &&
            $dayRows->isNotEmpty() &&
            $dayRows->every(fn ($row) => (bool) $row->is_closed);

        $scheduleStatus =
            $isDailyClosed || $isWeeklyClosed
                ? 'closed'
                : ($workingHours === [] ? 'not_configured' : 'open');

        /*
        |--------------------------------------------------------------------------
        | Get available slots
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'ok' => true,
            'date' => $date->toDateString(),
            'schedule' => [
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayNames[$dayOfWeek],
                'status' => $scheduleStatus,
                'is_closed' => $isDailyClosed || $isWeeklyClosed,
                'intervals' => $workingHours,
            ],
            'working_hours' => $workingHours,
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
        Request $request,
        Booking $booking
    ): View|RedirectResponse {
        $booking = $request
            ->user()
            ->bookings()
            ->with([
                'salon',
                'barber',
                'service',
            ])
            ->find($booking->id);

        if (!$booking) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'این نوبت متعلق به حساب شما نیست یا دیگر در دسترس نیست.'
                );
        }

        if ($booking->status !== BookingStatus::PENDING) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'این نوبت دیگر در وضعیت «در انتظار» نیست و قابل ویرایش نیست.'
                );
        }

        if (!$booking->salon?->is_active) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'سالن این نوبت دیگر در دسترس نیست.'
                );
        }

        $barbers = $booking->salon
            ->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

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
            $booking->status !== BookingStatus::PENDING
        ) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'فقط نوبت‌های در انتظار امکان ویرایش دارند.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        |
        | The service locks the booking and re-checks its status.
        | If the salon approved/cancelled it after this page was opened,
        | return a normal customer-facing message instead of a framework error.
        */

        try {
            $bookingService->updateByCustomer(
                $request->user(),
                $booking,
                $request->validated()
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()
                ->route('customer.dashboard')
                ->withErrors($exception->errors())
                ->with(
                    'error',
                    'نوبت در همین فاصله تغییر کرده است. لطفاً وضعیت جدید نوبت را بررسی کنید.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('customer.dashboard')
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
        Request $request,
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        try {
            $bookingService->cancelByCustomer(
                $request->user(),
                $booking
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()
                ->route('customer.dashboard')
                ->withErrors($exception->errors())
                ->with(
                    'error',
                    'وضعیت نوبت تغییر کرده است. لطفاً نوبت‌های خود را دوباره بررسی کنید.'
                );
        }

        return redirect()
            ->route('customer.dashboard')
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
        $ownedBooking = $request
            ->user()
            ->bookings()
            ->with('salon')
            ->find($booking->id);

        if (!$ownedBooking) {
            return response()->json([
                'ok' => false,
                'message' => 'این نوبت متعلق به حساب شما نیست.',
            ], 403);
        }

        if ($ownedBooking->status !== BookingStatus::PENDING) {
            return response()->json([
                'ok' => false,
                'message' => 'این نوبت دیگر قابل ویرایش نیست؛ وضعیت آن تغییر کرده است.',
            ], 409);
        }

        $salon = $ownedBooking->salon;

        if (!$salon?->is_active) {
            return response()->json([
                'ok' => false,
                'message' => 'سالن این نوبت دیگر در دسترس نیست.',
            ], 422);
        }

        $data = $request->validated();

        $barber = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->first();

        if (!$barber) {
            return response()->json([
                'ok' => false,
                'message' => 'آرایشگر انتخاب‌شده دیگر در این سالن فعال نیست.',
            ], 422);
        }

        $service = $salon
            ->services()
            ->whereKey($data['service_id'])
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return response()->json([
                'ok' => false,
                'message' => 'خدمت انتخاب‌شده دیگر در این سالن فعال نیست.',
            ], 422);
        }

        try {
            $date = Carbon::createFromFormat(
                'Y-m-d',
                $data['booking_date'],
                config('app.timezone', 'Asia/Tehran')
            )->startOfDay();
        } catch (\Throwable) {
            return response()->json([
                'ok' => false,
                'message' => 'تاریخ انتخاب‌شده معتبر نیست.',
            ], 422);
        }

        if ($date->lt(now(config('app.timezone', 'Asia/Tehran'))->startOfDay())) {
            return response()->json([
                'ok' => false,
                'message' => 'امکان انتخاب تاریخ گذشته وجود ندارد.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'date' => $date->toDateString(),
            'slots' => $availability->slots(
                $salon,
                $barber,
                $service,
                $date,
                $ownedBooking->id
            ),
        ]);
    }
}
