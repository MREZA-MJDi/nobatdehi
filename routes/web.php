<?php

use App\Enums\UserRole;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\SmsWebhookController;
use App\Http\Controllers\Admin\SalonController as AdminSalonController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

use App\Http\Controllers\PublicSite\DiscoverController;
use App\Http\Controllers\PublicSite\SalonController as PublicSalonController;

use App\Http\Controllers\Salon\BarberController as SalonBarberController;
use App\Http\Controllers\Salon\BookingController as SalonBookingController;
use App\Http\Controllers\Salon\DashboardController as SalonDashboardController;
use App\Http\Controllers\Salon\NotificationController as SalonNotificationController;
use App\Http\Controllers\Salon\PasswordController as SalonPasswordController;
use App\Http\Controllers\Salon\ReviewController as SalonReviewController;
use App\Http\Controllers\Salon\PostController as SalonPostController;
use App\Http\Controllers\Salon\ServiceController as SalonServiceController;
use App\Http\Controllers\Salon\SettingsController;
use App\Http\Controllers\Salon\WorkingHourController as SalonWorkingHourController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('brand-intro');
})->name('brand.intro');

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


Route::post(
    '/webhooks/sms/booking-reply',
    [SmsWebhookController::class, 'bookingReply']
)->name('webhooks.sms.booking-reply');


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

    Route::get(
        '/password/forgot',
        [PasswordResetController::class, 'create']
    )->name('password.request');

    Route::post(
        '/password/forgot',
        [PasswordResetController::class, 'sendOtp']
    )->middleware('throttle:5,1')
        ->name('password.email');

    Route::get(
        '/password/reset',
        [PasswordResetController::class, 'showReset']
    )->name('password.reset');

    Route::post(
        '/password/reset',
        [PasswordResetController::class, 'reset']
    )->middleware('throttle:10,1')
        ->name('password.update');

    Route::post(
        '/password/reset/resend',
        [PasswordResetController::class, 'resend']
    )->middleware('throttle:5,1')
        ->name('password.reset.resend');
});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ROLE-AWARE DASHBOARD ENTRY
    |--------------------------------------------------------------------------
    |
    | Provides one stable authenticated entry point for guest middleware,
    | old links and direct /dashboard visits. It never renders another role's UI.
    |
    */

    Route::get('/dashboard', function () {
        return match (request()->user()->role) {
            UserRole::SUPER_ADMIN => redirect()->route('admin.dashboard'),
            UserRole::SALON_OWNER => redirect()->route('salon.dashboard'),
            UserRole::CUSTOMER => redirect()->route('customer.dashboard'),
            UserRole::BARBER => redirect()->route('brand.intro'),
            default => redirect()->route('brand.intro'),
        };
    })->name('dashboard');

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
            Route::get(
                '/bookings/{booking}/edit',
                [CustomerBookingController::class, 'edit']
            )->name('bookings.edit');

            Route::put(
                '/bookings/{booking}',
                [CustomerBookingController::class, 'update']
            )->name('bookings.update');

            Route::delete(
                '/bookings/{booking}',
                [CustomerBookingController::class, 'cancel']
            )->name('bookings.cancel');

            Route::get(
                '/bookings/{booking}/availability',
                [CustomerBookingController::class, 'editAvailability']
            )->name('bookings.edit-availability');

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

            /*
            |--------------------------------------------------------------------------
            | Initial Password
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/password',
                [SalonPasswordController::class, 'edit']
            )->name('password.edit');

            Route::put(
                '/password',
                [SalonPasswordController::class, 'update']
            )->name('password.update');


            /*
            |--------------------------------------------------------------------------
            | Salon Owner Application
            |--------------------------------------------------------------------------
            */

            Route::middleware('password.changed')->group(function () {

            Route::get(
                '/dashboard',
                SalonDashboardController::class
            )->name('dashboard');

            Route::get(
                '/dashboard/data',
                [SalonDashboardController::class, 'data']
            )->name('dashboard.data');

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
                'posts',
                SalonPostController::class
            )->except('show');

            Route::patch(
                '/posts/{post}/toggle',
                [SalonPostController::class, 'toggle']
            )->name('posts.toggle');

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
            Route::get(
                '/settings',
                [SettingsController::class, 'edit']
            )->name('settings.edit');

            Route::put(
                '/settings',
                [SettingsController::class, 'update']
            )->name('settings.update');
            });

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
            )->name('brand.intro');

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
