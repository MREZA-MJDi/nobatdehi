<?php

use App\Enums\UserRole;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SalonController as AdminSalonController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

use App\Http\Controllers\PublicSite\DiscoverController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\SalonController as PublicSalonController;

use App\Http\Controllers\Salon\BarberController as SalonBarberController;
use App\Http\Controllers\Salon\BookingController as SalonBookingController;
use App\Http\Controllers\Salon\DashboardController as SalonDashboardController;
use App\Http\Controllers\Salon\NotificationController as SalonNotificationController;
use App\Http\Controllers\Salon\PortfolioItemController as SalonPortfolioController;
use App\Http\Controllers\Salon\ReviewController as SalonReviewController;
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
    [HomeController::class, 'index']
)->name('home');

Route::get(
    '/salons/discover',
    [DiscoverController::class, 'index']
)->name('salons.discover');


/*
|--------------------------------------------------------------------------
| PUBLIC SALON
|--------------------------------------------------------------------------
*/

Route::prefix('salons/{salon}')
    ->name('public.salons.')
    ->group(function () {

        Route::get(
            '/',
            [PublicSalonController::class, 'show']
        )->name('show');

        Route::get(
            '/booking',
            [CustomerBookingController::class, 'create']
        )->name('booking.create');

        Route::get(
            '/booking/availability',
            [CustomerBookingController::class, 'availability']
        )->name('booking.availability');

        Route::post(
            '/booking/prepare',
            [CustomerBookingController::class, 'prepare']
        )->name('booking.prepare');
    });


/*
|--------------------------------------------------------------------------
| GUEST AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [LoginController::class, 'create']
    )->name('login');

    Route::post(
        '/login',
        [LoginController::class, 'store']
    )->name('login.store');

    Route::get(
        '/register',
        [RegisterController::class, 'create']
    )->name('register');

    Route::post(
        '/register',
        [RegisterController::class, 'store']
    )->name('register.store');

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
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

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
                [CustomerDashboardController::class, 'index']
            )->name('dashboard');

            Route::get(
                '/bookings',
                [CustomerDashboardController::class, 'bookings']
            )->name('bookings.index');

            Route::get(
                '/profile',
                [CustomerProfileController::class, 'edit']
            )->name('profile.edit');

            Route::patch(
                '/profile',
                [CustomerProfileController::class, 'update']
            )->name('profile.update');

            Route::get(
                '/notifications',
                [CustomerNotificationController::class, 'index']
            )->name('notifications.index');

            Route::patch(
                '/notifications/{notification}/read',
                [CustomerNotificationController::class, 'read']
            )->name('notifications.read');

            Route::patch(
                '/notifications/read-all',
                [CustomerNotificationController::class, 'readAll']
            )->name('notifications.read-all');


            /*
            | Booking
            */

            Route::get(
                '/bookings/confirm',
                [CustomerBookingController::class, 'confirm']
            )->name('bookings.confirm');

            Route::post(
                '/bookings',
                [CustomerBookingController::class, 'store']
            )->name('bookings.store');


            /*
            | Reviews
            */

            Route::get(
                '/bookings/{booking}/review',
                [CustomerReviewController::class, 'create']
            )->name('bookings.review.create');

            Route::post(
                '/bookings/{booking}/review',
                [CustomerReviewController::class, 'store']
            )->name('bookings.review.store');
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

            Route::get(
                '/dashboard',
                SalonDashboardController::class
            )->name('dashboard');

            Route::resource(
                'barbers',
                SalonBarberController::class
            )->except('show');

            Route::resource(
                'services',
                SalonServiceController::class
            )->except('show');

            Route::get(
                '/working-hours',
                [SalonWorkingHourController::class, 'edit']
            )->name('working-hours.edit');

            Route::put(
                '/working-hours',
                [SalonWorkingHourController::class, 'update']
            )->name('working-hours.update');

            Route::post(
                '/working-hours/apply-default',
                [SalonWorkingHourController::class, 'applyDefault']
            )->name('working-hours.apply-default');

            Route::get(
                '/bookings',
                [SalonBookingController::class, 'index']
            )->name('bookings.index');

            Route::get(
                '/bookings/create',
                [SalonBookingController::class, 'create']
            )->name('bookings.create');

            Route::get(
                '/bookings/availability',
                [SalonBookingController::class, 'availability']
            )->name('bookings.availability');

            Route::post(
                '/bookings',
                [SalonBookingController::class, 'storeManual']
            )->name('bookings.store-manual');

            Route::get(
                '/bookings/{booking}',
                [SalonBookingController::class, 'show']
            )->name('bookings.show');

            Route::patch(
                '/bookings/{booking}/status',
                [SalonBookingController::class, 'updateStatus']
            )->name('bookings.status');

            Route::resource(
                'portfolio',
                SalonPortfolioController::class
            )->except('show');

            Route::get(
                '/reviews',
                [SalonReviewController::class, 'index']
            )->name('reviews.index');

            Route::patch(
                '/reviews/{review}/publish',
                [SalonReviewController::class, 'togglePublished']
            )->name('reviews.publish');

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
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        'role:' . UserRole::SUPER_ADMIN->value
    )
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
