<?php

use App\Enums\UserRole;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SalonController as AdminSalonController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

use App\Http\Controllers\PublicSite\DiscoverController;
use App\Http\Controllers\PublicSite\SalonController as PublicSalonController;

use App\Http\Controllers\Salon\BarberController as SalonBarberController;
use App\Http\Controllers\Salon\BookingController as SalonBookingController;
use App\Http\Controllers\Salon\DashboardController as SalonDashboardController;
use App\Http\Controllers\Salon\NotificationController as SalonNotificationController;
use App\Http\Controllers\Salon\ServiceController as SalonServiceController;
use App\Http\Controllers\Salon\WorkingHourController as SalonWorkingHourController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [DiscoverController::class, 'index']
)->name('home');

Route::get(
    '/salons/discover',
    [DiscoverController::class, 'index']
)->name('salons.discover');


/*
|--------------------------------------------------------------------------
| PUBLIC SALON
|--------------------------------------------------------------------------
|
| Public salon page and booking flow.
|
*/

Route::get(
    '/salons/{salon}/booking',
    [CustomerBookingController::class, 'create']
)->name('public.salons.booking.create');

Route::get(
    '/salons/{salon}/booking/availability',
    [CustomerBookingController::class, 'availability']
)->name('public.salons.booking.availability');

Route::post(
    '/salons/{salon}/booking/prepare',
    [CustomerBookingController::class, 'prepare']
)->name('public.salons.booking.prepare');

Route::get(
    '/salons/{salon}',
    [PublicSalonController::class, 'show']
)->name('public.salons.show');


/*
|--------------------------------------------------------------------------
| GUEST AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login',
        [LoginController::class, 'create']
    )->name('login');

    Route::post(
        '/login',
        [LoginController::class, 'store']
    )->name('login.store');


    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/register',
        [RegisterController::class, 'create']
    )->name('register');

    Route::post(
        '/register',
        [RegisterController::class, 'store']
    )->name('register.store');


    /*
    |--------------------------------------------------------------------------
    | Register OTP
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/register/verify',
        [RegisterController::class, 'showVerify']
    )->name('register.verify');

    Route::post(
        '/register/verify',
        [RegisterController::class, 'verify']
    )->name('register.verify.store');

    Route::post(
        '/register/verify/resend',
        [RegisterController::class, 'resend']
    )->name('register.verify.resend');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [LogoutController::class, 'destroy']
    )->name('logout');


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        'role:' . UserRole::CUSTOMER->value
    )
        ->prefix('customer')
        ->name('customer.')
        ->group(function () {

            Route::get(
                '/dashboard',
                CustomerDashboardController::class
            )->name('dashboard');

            Route::get(
                '/bookings/confirm',
                [CustomerBookingController::class, 'confirm']
            )->name('bookings.confirm');

            Route::post(
                '/bookings',
                [CustomerBookingController::class, 'store']
            )->name('bookings.store');
        });


    /*
    |--------------------------------------------------------------------------
    | SALON OWNER
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        'role:' . UserRole::SALON_OWNER->value
    )
        ->prefix('salon')
        ->name('salon.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard',
                SalonDashboardController::class
            )->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | Barbers
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'barbers',
                SalonBarberController::class
            )->except([
                'show',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'services',
                SalonServiceController::class
            )->except([
                'show',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Working Hours
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/working-hours',
                [SalonWorkingHourController::class, 'edit']
            )->name('working-hours.edit');

            Route::put(
                '/working-hours',
                [SalonWorkingHourController::class, 'update']
            )->name('working-hours.update');


            /*
            |--------------------------------------------------------------------------
            | Bookings
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/bookings',
                [SalonBookingController::class, 'index']
            )->name('bookings.index');

            Route::get(
                '/bookings/{booking}',
                [SalonBookingController::class, 'show']
            )->name('bookings.show');

            Route::patch(
                '/bookings/{booking}/status',
                [SalonBookingController::class, 'updateStatus']
            )->name('bookings.status');


            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/notifications',
                [SalonNotificationController::class, 'index']
            )->name('notifications.index');

            Route::patch(
                '/notifications/{notification}/read',
                [SalonNotificationController::class, 'read']
            )->name('notifications.read');

            Route::patch(
                '/notifications/read-all',
                [SalonNotificationController::class, 'readAll']
            )->name('notifications.read-all');
        });


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |--------------------------------------------------------------------------
    |
    | Super Admin manages salons only.
    |
    */

    Route::middleware([
        'role:' . UserRole::SUPER_ADMIN->value,
    ])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get(
                '/',
                fn () => redirect()->route('admin.dashboard')
            )->name('home');

            Route::get(
                '/dashboard',
                AdminDashboardController::class
            )->name('dashboard');

            Route::resource(
                'salons',
                AdminSalonController::class
            );
        });
});
