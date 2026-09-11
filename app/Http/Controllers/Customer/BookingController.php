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
    /**
     * Legacy / dedicated booking page.
     *
     * The new public Salon booking flow will primarily use
     * the booking modal, but this endpoint can remain available.
     */
    public function create(Salon $salon): View
    {
        abort_unless($salon->is_active, 404);

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

        return view('public.booking', [
            'salon' => $salon,
            'barbers' => $barbers,
            'services' => $services,
        ]);
    }

    /**
     * Return availability for a salon/barber/service/date combination.
     */
    public function availability(
        BookingAvailabilityRequest $request,
        Salon $salon,
        AvailabilityService $availability
    ): JsonResponse {
        abort_unless($salon->is_active, 404);

        $data = $request->validated();

        $barber = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $service = $salon
            ->services()
            ->whereKey($data['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $data['booking_date']
        );

        return response()->json([
            'slots' => $availability->slots(
                $salon,
                $barber,
                $service,
                $date
            ),
        ]);
    }

    /**
     * Store temporary booking data in session.
     *
     * This is currently kept for the existing confirmation flow.
     * The public Salon modal can later use the same validated data
     * without requiring a separate confirmation page.
     */
    public function prepare(
        BookingPrepareRequest $request,
        Salon $salon
    ): RedirectResponse {
        abort_unless($salon->is_active, 404);

        $data = $request->validated();

        if ((int) $data['salon_id'] !== (int) $salon->id) {
            abort(404);
        }

        $request->session()->put(
            'booking.pending',
            $data
        );

        if (!$request->user()) {
            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'برای ثبت نهایی نوبت وارد حساب خود شوید.'
                );
        }

        if (!$request->user()->isCustomer()) {
            return redirect()
                ->route('brand.intro')
                ->with(
                    'error',
                    'این حساب امکان رزرو مشتری را ندارد.'
                );
        }

        return redirect()->route(
            'customer.bookings.confirm'
        );
    }

    /**
     * Existing dedicated confirmation page.
     */
    public function confirm(
        Request $request
    ): View|RedirectResponse {
        $pending = $request
            ->session()
            ->get('booking.pending');

        if (!is_array($pending)) {
            return redirect()
                ->route('salons.discover')
                ->with(
                    'error',
                    'اطلاعات رزرو پیدا نشد.'
                );
        }

        $salon = Salon::query()
            ->whereKey($pending['salon_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $barber = $salon
            ->barbers()
            ->whereKey($pending['barber_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $service = $salon
            ->services()
            ->whereKey($pending['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return view(
            'customer.bookings.confirm',
            compact(
                'salon',
                'barber',
                'service',
                'pending'
            )
        );
    }

    /**
     * Store the booking after final validation.
     */
    public function store(
        BookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {
        $pending = $request
            ->session()
            ->get('booking.pending');

        if (!is_array($pending)) {
            return redirect()
                ->route('salons.discover')
                ->with(
                    'error',
                    'اطلاعات رزرو پیدا نشد.'
                );
        }

        $data = $request->validated();

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
                    ->route('customer.bookings.confirm')
                    ->withErrors([
                        'booking' =>
                            'اطلاعات رزرو تغییر کرده است. دوباره انتخاب کنید.',
                    ]);
            }
        }

        $booking = $bookingService->create(
            $request->user(),
            $data
        );

        $request
            ->session()
            ->forget('booking.pending');

        return redirect()
            ->route('customer.dashboard')
            ->with(
                'success',
                'نوبت شما با موفقیت ثبت شد.'
            );
    }

    /**
     * Edit a customer's pending booking.
     */
    public function edit(
        Booking $booking
    ): View {
        if (
            (int) $booking->customer_id !==
            (int) auth()->id()
        ) {
            abort(403);
        }

        abort_unless(
            $booking->status === BookingStatus::PENDING,
            404
        );

        $booking->load([
            'salon',
            'barber',
            'service',
        ]);

        abort_unless(
            $booking->salon?->is_active,
            404
        );

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

        return view('customer.bookings.edit', [
            'booking' => $booking,
            'salon' => $booking->salon,
            'barbers' => $barbers,
            'services' => $services,
        ]);
    }

    /**
     * Update a customer's pending booking.
     */
    public function update(
        BookingRequest $request,
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        if (
            (int) $booking->customer_id !==
            (int) $request->user()->id
        ) {
            abort(403);
        }

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

        $bookingService->updateByCustomer(
            $request->user(),
            $booking,
            $request->validated()
        );

        return redirect()
            ->route('customer.dashboard')
            ->with(
                'success',
                'نوبت شما با موفقیت ویرایش شد.'
            );
    }

    /**
     * Cancel a customer's pending booking.
     */
    public function cancel(
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        if (
            (int) $booking->customer_id !==
            (int) auth()->id()
        ) {
            abort(403);
        }

        if (
            $booking->status !== BookingStatus::PENDING
        ) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'فقط نوبت‌های در انتظار امکان لغو دارند.'
                );
        }

        $bookingService->changeStatus(
            $booking,
            BookingStatus::CANCELLED
        );

        return redirect()
            ->route('customer.dashboard')
            ->with(
                'success',
                'نوبت شما لغو شد.'
            );
    }

    /**
     * Return availability while editing an existing booking.
     *
     * The current booking is ignored when calculating conflicts.
     */
    public function editAvailability(
        BookingAvailabilityRequest $request,
        Booking $booking,
        AvailabilityService $availability
    ): JsonResponse {
        if (
            (int) $booking->customer_id !==
            (int) auth()->id()
        ) {
            abort(403);
        }

        abort_unless(
            $booking->status === BookingStatus::PENDING,
            404
        );

        $data = $request->validated();

        $salon = $booking->salon;

        abort_unless(
            $salon?->is_active,
            404
        );

        $barber = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $service = $salon
            ->services()
            ->whereKey($data['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $data['booking_date']
        );

        return response()->json([
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
