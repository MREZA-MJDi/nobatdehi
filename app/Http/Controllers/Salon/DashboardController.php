<?php

namespace App\Http\Controllers\Salon;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\ManualBookingRequest;
use App\Models\Salon;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $salon = $this->managedSalon($request);

        $dashboard = $this->buildDashboardData($salon);

        return view(
            'salon.dashboard',
            $dashboard
        );
    }

    public function storeManualBooking(
        ManualBookingRequest $request,
        BookingService $bookingService
    ): JsonResponse {
        $salon = $this->managedSalon($request);

        $data = $request->validated();

        $booking = $bookingService->createManual(
            $request->user(),
            [
                ...$data,
                'salon_id' => $salon->id,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'نوبت دستی با موفقیت ثبت و تأیید شد.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status->value,
                'booking_date' => $booking->booking_date,
                'start_time' => substr((string) $booking->start_time, 0, 5),
            ],
        ], 201);
    }

    public function data(Request $request): JsonResponse
    {
        $salon = $this->managedSalon($request);

        return response()->json([
            'ok' => true,
            'data' => $this->buildDashboardData($salon, true),
        ]);
    }

    private function persianWeekday(int $index): string
    {
        return [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه',
        ][$index] ?? '';
    }

    private function persianMonthLabel(Carbon $date): string
    {
        $months = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        $jalali = jalali_date($date->toDateString());
        $parts = explode('/', $jalali);
        $month = isset($parts[1]) ? (int) $parts[1] : null;

        return $months[$month] ?? $jalali;
    }

    private function managedSalon(Request $request): Salon
    {
        return $request->user()
            ->managedSalons()
            ->with([
                'workingHours',
                'dailyStatuses',
            ])
            ->withCount([
                'barbers',
                'services',
                'bookings',
            ])
            ->firstOrFail();
    }

    private function buildDashboardData(
        Salon $salon,
        bool $forApi = false
    ): array {
        $timezone = config(
            'app.timezone',
            'Asia/Tehran'
        );

        $today = Carbon::now($timezone)->startOfDay();

        // Business week starts on Saturday.
        $daysSinceSaturday =
            ($today->dayOfWeek + 1) % 7;

        $weekStart = $today
            ->copy()
            ->subDays($daysSinceSaturday);

        $monthStart = $today
            ->copy()
            ->startOfMonth();

        $unreadNotifications =
            auth()->user()
                ->unreadNotifications()
                ->count();

        $todayBookings = $salon->bookings()
            ->whereDate(
                'booking_date',
                $today->toDateString()
            )
            ->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED,
            ])
            ->count();

        $pendingBookings = $salon->bookings()
            ->where(
                'status',
                BookingStatus::PENDING
            )
            ->count();

        $confirmedToday = $salon->bookings()
            ->whereDate(
                'booking_date',
                $today->toDateString()
            )
            ->where(
                'status',
                BookingStatus::CONFIRMED
            )
            ->count();

        $completedToday = $salon->bookings()
            ->whereDate(
                'booking_date',
                $today->toDateString()
            )
            ->where(
                'status',
                BookingStatus::COMPLETED
            )
            ->count();

        $cancelledToday = $salon->bookings()
            ->whereDate(
                'booking_date',
                $today->toDateString()
            )
            ->where(
                'status',
                BookingStatus::CANCELLED
            )
            ->count();

        // Realized revenue: only completed appointments count as revenue.
        $revenueStatuses = [
            BookingStatus::COMPLETED,
        ];

        $todayRevenue = (int) $salon->bookings()
            ->whereDate(
                'booking_date',
                $today->toDateString()
            )
            ->whereIn(
                'status',
                $revenueStatuses
            )
            ->sum('price');

        $weekRevenue = (int) $salon->bookings()
            ->whereBetween(
                'booking_date',
                [
                    $weekStart->toDateString(),
                    $today->toDateString(),
                ]
            )
            ->whereIn(
                'status',
                $revenueStatuses
            )
            ->sum('price');

        $monthRevenue = (int) $salon->bookings()
            ->whereBetween(
                'booking_date',
                [
                    $monthStart->toDateString(),
                    $today->toDateString(),
                ]
            )
            ->whereIn(
                'status',
                $revenueStatuses
            )
            ->sum('price');

        $monthBookings = $salon->bookings()
            ->whereBetween(
                'booking_date',
                [
                    $monthStart->toDateString(),
                    $today->toDateString(),
                ]
            )
            ->count();

        $activeBarbers = $salon->barbers()
            ->where(
                'is_active',
                true
            )
            ->count();

        $activeServices = $salon->services()
            ->where(
                'is_active',
                true
            )
            ->count();

        $nowTime = Carbon::now($timezone)->format('H:i:s');

        $upcomingBookings = $salon->bookings()
            ->with([
                'customer:id,name,phone',
                'barber:id,name',
                'service:id,name,duration_minutes,price',
            ])
            ->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED,
            ])
            ->where(function ($query) use ($today, $nowTime) {
                $query
                    ->whereDate('booking_date', '>', $today->toDateString())
                    ->orWhere(function ($query) use ($today, $nowTime) {
                        $query
                            ->whereDate('booking_date', $today->toDateString())
                            ->where('start_time', '>=', $nowTime);
                    });
            })
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->limit(6)
            ->get();

        $recentBookings = $salon->bookings()
            ->with([
                'customer:id,name',
                'barber:id,name',
                'service:id,name',
            ])
            ->latest('id')
            ->limit(5)
            ->get();

        $nextBooking = $upcomingBookings->first();

        $dayOfWeek =
            ($today->dayOfWeek + 1) % 7;

        $todayDailyStatus = $salon->dailyStatuses
            ->firstWhere(
                fn ($status) =>
                    optional($status->date)->toDateString() === $today->toDateString()
            );

        $todayHours = $salon->workingHours
            ->where('day_of_week', $dayOfWeek)
            ->sortBy([
                [
                    'is_closed',
                    'asc',
                ],
                [
                    'start_time',
                    'asc',
                ],
            ])
            ->filter(
                fn ($row) =>
                    ! $row->is_closed &&
                    $row->start_time &&
                    $row->end_time
            )
            ->map(
                fn ($row) => [
                    'start' =>
                        substr(
                            (string) $row->start_time,
                            0,
                            5
                        ),
                    'end' =>
                        substr(
                            (string) $row->end_time,
                            0,
                            5
                        ),
                ]
            )
            ->unique(fn ($hour) => $hour['start'] . '|' . $hour['end'])
            ->values();

        $todayIsClosed =
            (bool) ($todayDailyStatus?->is_closed)
            || $todayHours->isEmpty();

        if ($todayDailyStatus?->is_closed) {
            $todayHours = collect();
        }

        $hasWorkingHours =
            $salon->workingHours->contains(
                fn ($row) =>
                    ! $row->is_closed &&
                    $row->start_time &&
                    $row->end_time
            );

        /*
        |--------------------------------------------------------------------------
        | Revenue charts
        |--------------------------------------------------------------------------
        |
        | Revenue is realized only when a booking becomes COMPLETED.
        | We expose three business views:
        | - daily: last 7 calendar days
        | - monthly: last 12 Jalali months
        | - yearly: last 5 Jalali years
        |--------------------------------------------------------------------------
        */

        $chartStart = $today
            ->copy()
            ->subYears(5)
            ->startOfYear();

        $revenueRows = $salon->bookings()
            ->whereBetween(
                'booking_date',
                [
                    $chartStart->toDateString(),
                    $today->toDateString(),
                ]
            )
            ->whereIn(
                'status',
                $revenueStatuses
            )
            ->get([
                'booking_date',
                'price',
            ]);

        $revenueByDay = $revenueRows
            ->groupBy(
                fn ($booking) =>
                    Carbon::parse(
                        $booking->booking_date
                    )->toDateString()
            )
            ->map(
                fn ($rows) => (int) $rows->sum('price')
            );

        $revenueByJalaliMonth = $revenueRows
            ->groupBy(
                fn ($booking) =>
                    substr(
                        jalali_date($booking->booking_date),
                        0,
                        7
                    )
            )
            ->map(
                fn ($rows) => (int) $rows->sum('price')
            );

        $revenueByJalaliYear = $revenueRows
            ->groupBy(
                fn ($booking) =>
                    substr(
                        jalali_date($booking->booking_date),
                        0,
                        4
                    )
            )
            ->map(
                fn ($rows) => (int) $rows->sum('price')
            );

        $dailyRevenueChart =
            collect(range(6, 0))
                ->map(
                    function ($daysAgo) use (
                        $today,
                        $revenueByDay
                    ) {
                        $date =
                            $today
                                ->copy()
                                ->subDays($daysAgo);

                        return [
                            'date' =>
                                $date->toDateString(),
                            'value' =>
                                (int) (
                                    $revenueByDay[
                                        $date->toDateString()
                                    ] ?? 0
                                ),
                            'label' => jalali_date($date),
                        ];
                    }
                )
                ->values();

        $monthlyRevenueChart =
            collect(range(11, 0))
                ->map(
                    function ($monthsAgo) use (
                        $today,
                        $revenueByJalaliMonth
                    ) {
                        $month =
                            $today
                                ->copy()
                                ->startOfMonth()
                                ->subMonths($monthsAgo);

                        $jalali = explode(
                            '/',
                            jalali_date($month)
                        );

                        $key =
                            ($jalali[0] ?? '') . '/' .
                            ($jalali[1] ?? '');

                        return [
                            'month' => $key,
                            'date' =>
                                $month->toDateString(),
                            'value' =>
                                (int) (
                                    $revenueByJalaliMonth[
                                        $key
                                    ] ?? 0
                                ),
                            'label' => $key,
                        ];
                    }
                )
                ->values();

        $jalaliToday =
            explode(
                '/',
                jalali_date($today)
            );

        $currentJalaliYear =
            (int) ($jalaliToday[0] ?? 0);

        $yearlyRevenueChart =
            collect(range(4, 0))
                ->map(
                    function ($yearsAgo) use (
                        $currentJalaliYear,
                        $revenueByJalaliYear
                    ) {
                        $year =
                            $currentJalaliYear
                            - $yearsAgo;

                        $key = (string) $year;

                        return [
                            'year' => $key,
                            'value' =>
                                (int) (
                                    $revenueByJalaliYear[$key]
                                    ?? 0
                                ),
                            'label' => $key,
                        ];
                    }
                )
                ->values();

        $dailyRevenueMax = max(
            1,
            (int) $dailyRevenueChart->max('value')
        );

        $monthlyRevenueMax = max(
            1,
            (int) $monthlyRevenueChart->max('value')
        );

        $yearlyRevenueMax = max(
            1,
            (int) $yearlyRevenueChart->max('value')
        );

        $dailyTotal = (int) $dailyRevenueChart->sum('value');
        $monthlyTotal = (int) $monthlyRevenueChart->sum('value');
        $yearlyTotal = (int) $yearlyRevenueChart->sum('value');

        $data = [
            'salon' => $salon,
            'unreadNotifications' =>
                $unreadNotifications,
            'pendingBookings' =>
                $pendingBookings,
            'todayBookings' =>
                $todayBookings,
            'confirmedToday' =>
                $confirmedToday,
            'completedToday' =>
                $completedToday,
            'cancelledToday' =>
                $cancelledToday,
            'todayRevenue' =>
                $todayRevenue,
            'weekRevenue' =>
                $weekRevenue,
            'monthRevenue' =>
                $monthRevenue,
            'monthBookings' =>
                $monthBookings,
            'activeBarbers' =>
                $activeBarbers,
            'activeServices' =>
                $activeServices,
            'upcomingBookings' =>
                $upcomingBookings,
            'recentBookings' =>
                $recentBookings,
            'nextBooking' =>
                $nextBooking,
            'today' =>
                $today,
            'todayHours' =>
                $todayHours,
            'todayIsClosed' =>
                $todayIsClosed,
            'todayDailyNote' =>
                $todayDailyStatus?->note,
            'hasWorkingHours' =>
                $hasWorkingHours,
            'salonIsActive' =>
                (bool) $salon->is_active,
            'dailyRevenueChart' =>
                $dailyRevenueChart,
            'monthlyRevenueChart' =>
                $monthlyRevenueChart,
            'yearlyRevenueChart' =>
                $yearlyRevenueChart,
            'dailyRevenueMax' =>
                $dailyRevenueMax,
            'monthlyRevenueMax' =>
                $monthlyRevenueMax,
            'yearlyRevenueMax' =>
                $yearlyRevenueMax,
            'dailyTotal' =>
                $dailyTotal,
            'monthlyTotal' =>
                $monthlyTotal,
            'yearlyTotal' =>
                $yearlyTotal,
        ];

        if (! $forApi) {
            return $data;
        }

        return [
            'date' =>
                $today->toDateString(),
            'timezone' =>
                $timezone,
            'metrics' => [
                'todayBookings' =>
                    $todayBookings,
                'pendingBookings' =>
                    $pendingBookings,
                'confirmedToday' =>
                    $confirmedToday,
                'completedToday' =>
                    $completedToday,
                'cancelledToday' =>
                    $cancelledToday,
                'todayRevenue' =>
                    $todayRevenue,
                'weekRevenue' =>
                    $weekRevenue,
                'monthRevenue' =>
                    $monthRevenue,
                'monthBookings' =>
                    $monthBookings,
                'activeBarbers' =>
                    $activeBarbers,
                'activeServices' =>
                    $activeServices,
                'unreadNotifications' =>
                    $unreadNotifications,
            ],
            'today' => [
                'isClosed' =>
                    $todayIsClosed,
                'hours' =>
                    $todayHours
                        ->values()
                        ->all(),
                'note' =>
                    $todayDailyStatus?->note,
            ],
            'readiness' => [
                'salonActive' =>
                    (bool) $salon->is_active,
                'hasWorkingHours' =>
                    $hasWorkingHours,
                'activeBarbers' =>
                    $activeBarbers,
                'activeServices' =>
                    $activeServices,
                'readyToTakeBooking' =>
                    (bool) (
                        $salon->is_active
                        && $hasWorkingHours
                        && $activeBarbers > 0
                        && $activeServices > 0
                    ),
            ],
            'revenue' => [
                'daily' =>
                    $dailyRevenueChart
                        ->values()
                        ->all(),
                'monthly' =>
                    $monthlyRevenueChart
                        ->values()
                        ->all(),
                'yearly' =>
                    $yearlyRevenueChart
                        ->values()
                        ->all(),
            ],
            'recentBookings' =>
                $recentBookings
                    ->map(
                        fn ($booking) => [
                            'id' => $booking->id,
                            'date' => $booking->booking_date,
                            'startTime' => substr((string) $booking->start_time, 0, 5),
                            'customer' => $booking->customer?->name ?? $booking->customer_name ?? 'مشتری',
                            'service' => $booking->service?->name ?? 'خدمت',
                            'barber' => $booking->barber?->name ?? 'متخصص',
                            'status' => $booking->status instanceof BookingStatus
                                ? $booking->status->value
                                : (string) $booking->status,
                        ]
                    )
                    ->values()
                    ->all(),
            'upcomingBookings' =>
                $upcomingBookings
                    ->map(
                        fn ($booking) => [
                            'id' =>
                                $booking->id,
                            'date' =>
                                $booking
                                    ->booking_date,
                            'startTime' =>
                                substr(
                                    (string)
                                        $booking
                                            ->start_time,
                                    0,
                                    5
                                ),
                            'customer' =>
                                $booking
                                    ->customer?->name
                                    ?? $booking->customer_name
                                    ?? 'مشتری',
                            'service' =>
                                $booking
                                    ->service?->name
                                    ?? 'خدمت',
                            'barber' =>
                                $booking
                                    ->barber?->name
                                    ?? 'متخصص',
                            'status' =>
                                $booking->status
                                    instanceof BookingStatus
                                        ? $booking
                                            ->status
                                            ->value
                                        : (string)
                                            $booking->status,
                        ]
                    )
                    ->values()
                    ->all(),
        ];
    }
}
