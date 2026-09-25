<?php

namespace App\Http\Controllers\Salon;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingAvailabilityRequest;
use App\Http\Requests\Salon\BookingStatusRequest;
use App\Http\Requests\Salon\ManualBookingRequest;
use App\Models\Booking;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\Booking\BookingService;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());
        $dateInput = trim($request->string('date')->toString());
        $date = null;
        $dateError = null;

        $query = $salon->bookings()->with([
            'customer:id,name,phone',
            'barber:id,name',
            'service:id,name,duration_minutes,price',
        ]);

        if (in_array(
            $status,
            array_map(fn (BookingStatus $item) => $item->value, BookingStatus::cases()),
            true
        )) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('barber', function ($barberQuery) use ($search) {
                        $barberQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($dateInput !== '') {
            $rawDate = trim(strtr($dateInput, [
                '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4',
                '۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
                '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4',
                '٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            ]));

            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)
                ? $rawDate
                : gregorian_date($rawDate);

            if ($date) {
                $query->whereDate('booking_date', $date);
            } else {
                $dateError = 'تاریخ را به‌صورت شمسی معتبر مثل ۱۴۰۵/۰۷/۰۱ وارد کنید.';
            }
        }

        $bookings = $query
            ->orderByDesc('booking_date')
            ->orderBy('start_time')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'all' => $salon->bookings()->count(),
            'pending' => $salon->bookings()->where('status', BookingStatus::PENDING)->count(),
            'confirmed' => $salon->bookings()->where('status', BookingStatus::CONFIRMED)->count(),
            'completed' => $salon->bookings()->where('status', BookingStatus::COMPLETED)->count(),
            'cancelled' => $salon->bookings()->where('status', BookingStatus::CANCELLED)->count(),
        ];

        $today = now(config('app.timezone', 'Asia/Tehran'));

        $todayBookingsCount = $salon->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::CONFIRMED])
            ->count();

        return view(
            'salon.bookings.index',
            compact(
                'salon',
                'bookings',
                'stats',
                'todayBookingsCount',
                'status',
                'search',
                'dateInput',
                'dateError'
            )
        );
    }

    public function create(Request $request): View
    {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $unreadNotifications = $request->user()->unreadNotifications()->count();

        $barbers = $salon->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = $salon->services()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'salon.bookings.create',
            compact('salon', 'barbers', 'services', 'unreadNotifications')        );
    }

    public function manualData(Request $request): JsonResponse
    {
        $salon = $request->user()->managedSalons()->firstOrFail();

        return response()->json([
            'ok' => true,
            'data' => [
                'salon' => [
                    'id' => $salon->id,
                    'name' => $salon->name,
                    'isActive' => (bool) $salon->is_active,
                ],
                'barbers' => $salon->barbers()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn ($barber) => [
                        'id' => (int) $barber->id,
                        'name' => $barber->name,
                    ])
                    ->values()
                    ->all(),
                'services' => $salon->services()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name', 'price', 'duration_minutes'])
                    ->map(fn ($service) => [
                        'id' => (int) $service->id,
                        'name' => $service->name,
                        'price' => (int) $service->price,
                        'duration' => (int) $service->duration_minutes,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function availability(
        BookingAvailabilityRequest $request,
        AvailabilityService $availability
    ): JsonResponse {
        $salon = $request->user()->managedSalons()->firstOrFail();
        $data = $request->validated();

        $barber = $salon->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->first();

        if (!$barber) {
            return response()->json([
                'ok' => false,
                'message' => 'آرایشگر انتخاب شده برای این سالن معتبر نیست.',
            ], 422);
        }

        $service = $salon->services()
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
            $date = Carbon::createFromFormat('Y-m-d', $data['booking_date'], $timezone)->startOfDay();
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

        $schedule = $availability->daySchedule(
            $salon,
            $barber,
            $date
        );

        $slots = $availability->slots($salon, $barber, $service, $date);

        return response()
            ->json([
                'ok' => true,
                'date' => $date->toDateString(),
                'schedule' => $schedule,
                'working_hours' => $schedule['intervals'],
                'breaks' => $schedule['breaks'],
                'slots' => $slots,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function storeManual(
        ManualBookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {
        $salon = $request->user()->managedSalons()->firstOrFail();
        $data = $request->validated();

        $bookingService->createManual(
            $request->user(),
            [
                ...$data,
                'salon_id' => $salon->id,
            ]
        );

        return redirect()
            ->route('salon.bookings.index')
            ->with('success', 'نوبت دستی با موفقیت ثبت و تأیید شد.');
    }

    public function show(Request $request, Booking $booking): View
    {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $booking = $salon->bookings()
            ->with(['customer', 'barber', 'service'])
            ->findOrFail($booking->id);

        return view('salon.bookings.show', compact('salon', 'booking'));
    }

    public function updateStatus(
        BookingStatusRequest $request,
        Booking $booking,
        BookingService $bookingService
    ): RedirectResponse {
        $salon = $request->user()->managedSalons()->firstOrFail();

        $booking = $salon->bookings()
            ->with(['customer', 'barber', 'service'])
            ->findOrFail($booking->id);

        $status = BookingStatus::from($request->validated('status'));

        $bookingService->changeStatus($booking, $status);

        return redirect()
            ->route('salon.bookings.show', $booking)
            ->with(
                'success',
                match ($status) {
                    BookingStatus::CONFIRMED => 'نوبت با موفقیت تأیید شد.',
                    BookingStatus::COMPLETED => 'نوبت به‌عنوان تکمیل‌شده ثبت شد.',
                    BookingStatus::CANCELLED => 'نوبت با موفقیت لغو شد.',
                }
            );
    }
}
