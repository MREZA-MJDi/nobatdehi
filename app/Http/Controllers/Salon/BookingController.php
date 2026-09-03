<?php

namespace App\Http\Controllers\Salon;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingAvailabilityRequest;
use App\Http\Requests\Salon\BookingStatusRequest;
use App\Http\Requests\Salon\ManualBookingRequest;
use App\Models\Booking;
use App\Models\User;
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
    | Booking List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        $bookings = $salon
            ->bookings()
            ->with([
                'barber',
                'service',
                'customer',
            ])
            ->latest('booking_date')
            ->latest('start_time')
            ->paginate(20);


        return view(
            'salon.bookings.index',
            compact(
                'salon',
                'bookings'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Manual Booking Page
    |--------------------------------------------------------------------------
    |
    | Only customers who have a relationship with this salon
    | through an existing booking are shown.
    |
    */
    public function create(
        Request $request
    ): View {

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        $unreadNotifications = $request
            ->user()
            ->unreadNotifications()
            ->count();


        $barbers = $salon
            ->barbers()
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();


        $services = $salon
            ->services()
            ->where(
                'is_active',
                true
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();


        $customers = User::query()
            ->where(
                'role',
                'customer'
            )
            ->whereHas(
                'bookings',
                function ($query) use ($salon) {
                    $query->where(
                        'salon_id',
                        $salon->id
                    );
                }
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'phone',
            ])
            ->unique('id')
            ->values();


        return view(
            'salon.bookings.create',
            compact(
                'salon',
                'barbers',
                'services',
                'customers',
                'unreadNotifications'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Manual Booking Availability
    |--------------------------------------------------------------------------
    */

    public function availability(
        BookingAvailabilityRequest $request,
        AvailabilityService $availability
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Current Salon
        |--------------------------------------------------------------------------
        */

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Validated Data
        |--------------------------------------------------------------------------
        */

        $data =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Barber Belongs To Current Salon
        |--------------------------------------------------------------------------
        */

        $barber = $salon
            ->barbers()
            ->whereKey(
                $data['barber_id']
            )
            ->where(
                'is_active',
                true
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Service Belongs To Current Salon
        |--------------------------------------------------------------------------
        */

        $service = $salon
            ->services()
            ->whereKey(
                $data['service_id']
            )
            ->where(
                'is_active',
                true
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        $date =
            Carbon::createFromFormat(
                'Y-m-d',
                $data['booking_date']
            );


        /*
        |--------------------------------------------------------------------------
        | Availability
        |--------------------------------------------------------------------------
        */

        $slots =
            $availability->slots(
                $salon,
                $barber,
                $service,
                $date
            );


        return response()->json([
            'slots' => $slots,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Store Manual Booking
    |--------------------------------------------------------------------------
    */

    public function storeManual(
        ManualBookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Current Salon
        |--------------------------------------------------------------------------
        */

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Validated Request
        |--------------------------------------------------------------------------
        */

        $data =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Customer Security Check
        |--------------------------------------------------------------------------
        |
        | The selected customer MUST belong to this salon's customer base.
        |
        */

        $customer =
            User::query()
                ->whereKey(
                    $data['customer_id']
                )
                ->where(
                    'role',
                    'customer'
                )
                ->whereHas(
                    'bookings',
                    function ($query) use ($salon) {

                        $query->where(
                            'salon_id',
                            $salon->id
                        );

                    }
                )
                ->first();


        if (!$customer) {

            return back()
                ->withErrors([
                    'customer_id' =>
                        'این مشتری مربوط به این سالن نیست.',
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | Create Confirmed Manual Booking
        |--------------------------------------------------------------------------
        */

        $booking =
            $bookingService->create(
                $customer,
                [
                    ...$data,

                    'salon_id' =>
                        $salon->id,
                ],
                BookingStatus::CONFIRMED
            );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'salon.bookings.index'
            )
            ->with(
                'success',
                'نوبت دستی برای مشتری با موفقیت ثبت و تأیید شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Booking Details
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Booking $booking
    ): View {

        /*
        |--------------------------------------------------------------------------
        | Current Salon
        |--------------------------------------------------------------------------
        */

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Security:
        | Booking must belong to current salon
        |--------------------------------------------------------------------------
        */

        $booking =
            $salon
                ->bookings()
                ->with([
                    'barber',
                    'service',
                    'customer',
                ])
                ->findOrFail(
                    $booking->id
                );


        return view(
            'salon.bookings.show',
            compact(
                'salon',
                'booking'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Booking Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        BookingStatusRequest $request,
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Current Salon
        |--------------------------------------------------------------------------
        */

        $salon = $request
            ->user()
            ->managedSalons()
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Security:
        | Booking must belong to current salon
        |--------------------------------------------------------------------------
        */

        $booking =
            $salon
                ->bookings()
                ->findOrFail(
                    $booking->id
                );


        /*
        |--------------------------------------------------------------------------
        | New Status
        |--------------------------------------------------------------------------
        */

        $status =
            BookingStatus::from(
                $request->validated(
                    'status'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Change Status
        |--------------------------------------------------------------------------
        */

        $bookingService->changeStatus(
            $booking,
            $status
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return back()
            ->with(
                'success',
                'وضعیت نوبت با موفقیت تغییر کرد و مشتری مطلع شد.'
            );
    }
}

