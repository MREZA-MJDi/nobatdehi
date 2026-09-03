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
    /*
    |--------------------------------------------------------------------------
    | Booking Page
    |--------------------------------------------------------------------------
    */

    public function create(
        Salon $salon
    ): View {

        abort_unless(
            $salon->is_active,
            404
        );


        $barbers =
            $salon
                ->barbers()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get();


        $services =
            $salon
                ->services()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();


        return view(
            'public.booking',
            [
                'salon' =>
                    $salon,

                'barbers' =>
                    $barbers,

                'services' =>
                    $services,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    public function availability(
        BookingAvailabilityRequest $request,
        Salon $salon,
        AvailabilityService $availability
    ): JsonResponse {

        $data =
            $request->validated();


        abort_unless(
            $salon->is_active,
            404
        );


        $barber =
            $salon
                ->barbers()
                ->whereKey(
                    $data['barber_id']
                )
                ->where(
                    'is_active',
                    true
                )
                ->firstOrFail();


        $service =
            $salon
                ->services()
                ->whereKey(
                    $data['service_id']
                )
                ->where(
                    'is_active',
                    true
                )
                ->firstOrFail();


        $date =
            Carbon::createFromFormat(
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


    /*
    |--------------------------------------------------------------------------
    | Prepare Booking
    |--------------------------------------------------------------------------
    */

    public function prepare(
        BookingPrepareRequest $request,
        Salon $salon
    ): RedirectResponse {

        abort_unless(
            $salon->is_active,
            404
        );


        $data =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Prevent Fake Salon ID
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
        | Save Booking Intent
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'booking.pending',
                $data
            );


        /*
        |--------------------------------------------------------------------------
        | Guest
        |--------------------------------------------------------------------------
        */

        if (!$request->user()) {

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'برای ثبت نهایی نوبت وارد حساب خود شوید.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Customer Check
        |--------------------------------------------------------------------------
        */

        if (
            !$request
                ->user()
                ->isCustomer()
        ) {

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'این حساب امکان ثبت نوبت مشتری را ندارد.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Confirmation
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'customer.bookings.confirm'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Confirmation Page
    |--------------------------------------------------------------------------
    */

    public function confirm(
        Request $request
    ): View|RedirectResponse {

        $pending =
            $request
                ->session()
                ->get(
                    'booking.pending'
                );


        if (!$pending) {

            return redirect()
                ->route(
                    'salons.discover'
                )
                ->with(
                    'error',
                    'اطلاعات رزرو پیدا نشد.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Load Public Salon
        |--------------------------------------------------------------------------
        */

        $salon =
            Salon::query()
                ->whereKey(
                    $pending['salon_id']
                )
                ->where(
                    'is_active',
                    true
                )
                ->with([
                    'barbers',
                    'services',
                ])
                ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Load Barber
        |--------------------------------------------------------------------------
        */

        $barber =
            $salon
                ->barbers()
                ->whereKey(
                    $pending['barber_id']
                )
                ->where(
                    'is_active',
                    true
                )
                ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Load Service
        |--------------------------------------------------------------------------
        */

        $service =
            $salon
                ->services()
                ->whereKey(
                    $pending['service_id']
                )
                ->where(
                    'is_active',
                    true
                )
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


    /*
    |--------------------------------------------------------------------------
    | Store Final Booking
    |--------------------------------------------------------------------------
    */

    public function store(
        BookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {

        $pending =
            $request
                ->session()
                ->get(
                    'booking.pending'
                );


        if (!$pending) {

            return redirect()
                ->route(
                    'salons.discover'
                )
                ->with(
                    'error',
                    'اطلاعات رزرو پیدا نشد.'
                );

        }


        $data =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Prevent Intent Tampering
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
                        'public.salons.booking.create',
                        $pending['salon_id']
                    )
                    ->withErrors([
                        'booking' =>
                            'اطلاعات رزرو تغییر کرده است. دوباره انتخاب کنید.',
                    ]);

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Create Booking
        |--------------------------------------------------------------------------
        */

        $booking =
            $bookingService->create(
                $request->user(),
                $data
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Pending Intent
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'booking.pending'
            );


        /*
        |--------------------------------------------------------------------------
        | Done
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
}
