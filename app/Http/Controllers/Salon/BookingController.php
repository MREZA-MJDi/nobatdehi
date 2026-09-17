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
use App\Services\Booking\BookingConfirmationService;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());
        $date = $request->string('date')->toString();

        $query = $salon->bookings()->with([
            'customer:id,name,phone',
            'barber:id,name',
            'service:id,name,duration_minutes,price',
        ]);

        if (
            in_array(
                $status,
                array_map(
                    fn (BookingStatus $item): string => $item->value,
                    BookingStatus::cases()
                ),
                true
            )
        ) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query
                    ->whereHas('customer', function ($customerQuery) use ($search): void {
                        $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('service', function ($serviceQuery) use ($search): void {
                        $serviceQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('barber', function ($barberQuery) use ($search): void {
                        $barberQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $query->whereDate('booking_date', $date);
        }

        $bookings = $query
            ->orderByDesc('booking_date')
            ->orderBy('start_time')
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'all' => $salon->bookings()->count(),
            'pending' => $salon->bookings()->where('status', BookingStatus::PENDING)->count(),
            'confirmed' => $salon->bookings()->where('status', BookingStatus::CONFIRMED)->count(),
            'completed' => $salon->bookings()->where('status', BookingStatus::COMPLETED)->count(),
            'cancelled' => $salon->bookings()->where('status', BookingStatus::CANCELLED)->count(),
        ];

        $today = now(config('app.timezone'));

        $todayBookingsCount = $salon
            ->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED,
            ])
            ->count();

        return view('salon.bookings.index', compact(
            'salon',
            'bookings',
            'stats',
            'todayBookingsCount',
            'status',
            'search',
            'date'
        ));
    }

    public function create(Request $request): View
    {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $unreadNotifications = $request->user()->unreadNotifications()->count();

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

        /*
         * A manual booking is often used for a first-time customer.
         * The selector therefore must contain every active customer account,
         * not only people who already booked this salon.
         */
        $customers = User::query()
            ->where('role', 'customer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'phone'])
            ->values();

        return view('salon.bookings.create', compact(
            'salon',
            'barbers',
            'services',
            'customers',
            'unreadNotifications'
        ));
    }

    public function availability(
        BookingAvailabilityRequest $request,
        AvailabilityService $availability
    ): JsonResponse {
        $salon = $request->user()->managedSalons()->firstOrFail();
        $data = $request->validated();

        $barber = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->first();

        if (!$barber) {
            return response()->json([
                'ok' => false,
                'message' => 'آرایشگر انتخاب شده برای این سالن معتبر نیست.',
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
                'message' => 'خدمت انتخاب شده برای این سالن معتبر نیست.',
            ], 422);
        }

        $timezone = config('app.timezone', 'Asia/Tehran');

        try {
            $date = Carbon::createFromFormat(
                'Y-m-d',
                $data['booking_date'],
                $timezone
            )->startOfDay();
        } catch (\Throwable) {
            return response()->json([
                'ok' => false,
                'message' => 'تاریخ انتخاب شده معتبر نیست.',
            ], 422);
        }

        if ($date->lt(now($timezone)->startOfDay())) {
            return response()->json([
                'ok' => false,
                'message' => 'امکان انتخاب تاریخ گذشته وجود ندارد.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'date' => $date->toDateString(),
            'schedule' => $availability->schedule($salon, $date),
            'slots' => $availability->slots($salon, $barber, $service, $date),
        ]);
    }

    public function storeManual(
        ManualBookingRequest $request,
        BookingService $bookingService,
        BookingConfirmationService $confirmationService
    ): RedirectResponse {
        $salon = $request->user()->managedSalons()->firstOrFail();
        $data = $request->validated();

        $customer = User::query()
            ->whereKey($data['customer_id'])
            ->where('role', 'customer')
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            return back()
                ->withErrors([
                    'customer_id' => 'حساب مشتری انتخاب‌شده معتبر یا فعال نیست.',
                ])
                ->withInput();
        }

        $booking = $bookingService->createManual(
            $request->user(),
            [
                ...$data,
                'salon_id' => $salon->id,
                'customer' => $customer,
            ]
        );

        $confirmationService->reconcileManualConfirmation(
            $booking,
            $request->user()
        );

        return redirect()
            ->route('salon.bookings.index')
            ->with('success', 'نوبت دستی با موفقیت ثبت و تأیید شد.');
    }

    public function show(
        Request $request,
        Booking $booking,
        BookingConfirmationService $confirmationService
    ): View {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $booking = $salon
            ->bookings()
            ->with([
                'customer',
                'barber',
                'service',
                'confirmer',
            ])
            ->findOrFail($booking->id);

        $priority = null;

        if ($booking->status === BookingStatus::PENDING) {
            $priority = $confirmationService->priority($booking);
        }

        return view('salon.bookings.show', compact(
            'salon',
            'booking',
            'priority'
        ));
    }

    public function updateStatus(
        BookingStatusRequest $request,
        Booking $booking,
        BookingService $bookingService,
        BookingConfirmationService $confirmationService
    ): RedirectResponse {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $booking = $salon
            ->bookings()
            ->with([
                'customer',
                'barber',
                'service',
            ])
            ->findOrFail($booking->id);

        $data = $request->validated();
        $status = BookingStatus::from($data['status']);

        if ($status === BookingStatus::CONFIRMED) {
            $confirmationService->confirm(
                $booking,
                $request->user(),
                $request->boolean('override_priority'),
                $data['priority_override_reason'] ?? null
            );
        } else {
            $bookingService->changeStatus($booking, $status);
        }

        return redirect()
            ->route('salon.bookings.show', $booking)
            ->with('success', match ($status) {
                BookingStatus::CONFIRMED => 'نوبت با موفقیت تأیید شد.',
                BookingStatus::COMPLETED => 'نوبت به‌عنوان تکمیل‌شده ثبت شد.',
                BookingStatus::CANCELLED => 'نوبت با موفقیت لغو شد.',
                BookingStatus::PENDING => 'نوبت در انتظار تأیید است.',
            });
    }
}
