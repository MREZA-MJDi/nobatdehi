<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingAvailabilityRequest;
use App\Http\Requests\Customer\BookingPrepareRequest;
use App\Http\Requests\Customer\BookingRequest;
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
            'slots' =>
                $availability->slots(
                    $salon,
                    $barber,
                    $service,
                    $date
                ),
        ]);
    }


    public function prepare(
        BookingPrepareRequest $request,
        Salon $salon
    ): RedirectResponse {

        abort_unless(
            $salon->is_active,
            404
        );


        $data = $request->validated();


        if (
            (int) $data['salon_id'] !==
            (int) $salon->id
        ) {
            abort(404);
        }


        $request
            ->session()
            ->put(
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


        if (
            !$request->user()->isCustomer()
        ) {

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'این حساب امکان رزرو مشتری را ندارد.'
                );
        }


        return redirect()->route(
            'customer.bookings.confirm'
        );
    }


    public function confirm(
        Request $request
    ): View|RedirectResponse {

        $pending =
            $request
                ->session()
                ->get('booking.pending');


        if (
            !is_array($pending)
        ) {

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


    public function store(
        BookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {

        $pending =
            $request
                ->session()
                ->get('booking.pending');


        if (
            !is_array($pending)
        ) {

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
                    ->route(
                        'customer.bookings.confirm'
                    )
                    ->withErrors([
                        'booking' =>
                            'اطلاعات رزرو تغییر کرده است. دوباره انتخاب کنید.',
                    ]);
            }
        }


        $booking =
            $bookingService->create(
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
}
