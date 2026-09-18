<?php

namespace App\Http\Controllers\Salon;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $timezone = config('app.timezone', 'Asia/Tehran');
        $today = Carbon::now($timezone)->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $weekStart = $today->copy()->startOfWeek();

        $salon = $request->user()
            ->managedSalons()
            ->withCount(['barbers', 'services', 'bookings'])
            ->with(['workingHours'])
            ->firstOrFail();

        $unreadNotifications = $request->user()
            ->unreadNotifications()
            ->count();

        $todayBookings = $salon->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED,
            ])
            ->count();

        $pendingBookings = $salon->bookings()
            ->where('status', BookingStatus::PENDING)
            ->count();

        $confirmedToday = $salon->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->where('status', BookingStatus::CONFIRMED)
            ->count();

        $completedToday = $salon->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->where('status', BookingStatus::COMPLETED)
            ->count();

        $cancelledToday = $salon->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->where('status', BookingStatus::CANCELLED)
            ->count();

        $todayRevenue = (int) $salon->bookings()
            ->whereDate('booking_date', $today->toDateString())
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::COMPLETED,
            ])
            ->sum('price');

        $weekRevenue = (int) $salon->bookings()
            ->whereBetween('booking_date', [
                $weekStart->toDateString(),
                $today->toDateString(),
            ])
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::COMPLETED,
            ])
            ->sum('price');

        $monthRevenue = (int) $salon->bookings()
            ->whereBetween('booking_date', [
                $monthStart->toDateString(),
                $today->toDateString(),
            ])
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::COMPLETED,
            ])
            ->sum('price');

        $monthBookings = $salon->bookings()
            ->whereBetween('booking_date', [
                $monthStart->toDateString(),
                $today->toDateString(),
            ])
            ->count();

        $activeBarbers = $salon->barbers()
            ->where('is_active', true)
            ->count();

        $activeServices = $salon->services()
            ->where('is_active', true)
            ->count();

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
            ->whereDate('booking_date', '>=', $today->toDateString())
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

        $dayOfWeek = ($today->dayOfWeek + 1) % 7;

        $todayHours = $salon->workingHours
            ->where('day_of_week', $dayOfWeek)
            ->sortBy([
                ['is_closed', 'asc'],
                ['start_time', 'asc'],
            ])
            ->filter(fn ($row) =>
                !$row->is_closed &&
                $row->start_time &&
                $row->end_time
            )
            ->map(fn ($row) => [
                'start' => substr((string) $row->start_time, 0, 5),
                'end' => substr((string) $row->end_time, 0, 5),
            ])
            ->values();

        $todayIsClosed = $todayHours->isEmpty();

        return view('salon.dashboard', compact(
            'salon',
            'unreadNotifications',
            'pendingBookings',
            'todayBookings',
            'confirmedToday',
            'completedToday',
            'cancelledToday',
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'monthBookings',
            'activeBarbers',
            'activeServices',
            'upcomingBookings',
            'recentBookings',
            'nextBooking',
            'today',
            'todayHours',
            'todayIsClosed',
        ));
    }
}
