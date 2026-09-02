<?php

use App\Enums\UserRole;

use App\Http\Controllers\Admin\BarberController as AdminBarberController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SalonController as AdminSalonController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Barber\DashboardController as BarberDashboardController;

use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

use App\Http\Controllers\PublicSite\DiscoverController;
use App\Http\Controllers\PublicSite\SalonController as PublicSalonController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC WEBSITE
|--------------------------------------------------------------------------
|
| The public website starts from Discover.
|
*/


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
|
| nobatdehi.com
|      ↓
| Discover
|
*/

Route::get(
    '/',
    [DiscoverController::class, 'index']
)->name('home');


/*
|--------------------------------------------------------------------------
| Discover
|--------------------------------------------------------------------------
|
| Salon discovery only.
|
| IMPORTANT:
| Must be before /salons/{salon}.
|
*/

Route::get(
    '/salons/discover',
    [DiscoverController::class, 'index']
)->name('salons.discover');


/*
|--------------------------------------------------------------------------
| Public Salon
|--------------------------------------------------------------------------
|
| Example:
|
| /salons/noban
|
| {salon} is resolved using Salon::getRouteKeyName()
| and therefore uses slug.
|
*/

Route::get(
    '/salons/{salon}',
    [PublicSalonController::class, 'show']
)->name('public.salons.show');


/*
|--------------------------------------------------------------------------
| GUEST / AUTHENTICATION
|--------------------------------------------------------------------------
|
| One authentication flow:
|
| Phone
|   ↓
| OTP
|   ↓
| User Role
|
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
    | Login OTP
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login/verify',
        [LoginController::class, 'showVerify']
    )->name('login.verify');

    Route::post(
        '/login/verify',
        [LoginController::class, 'verify']
    )->name('login.verify.store');

    Route::post(
        '/login/verify/resend',
        [LoginController::class, 'resend']
    )->name('login.verify.resend');


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
    | BARBER
    |--------------------------------------------------------------------------
    |
    | role = barber
    |
    */

    Route::middleware(
        'role:' . UserRole::BARBER->value
    )->group(function () {

        Route::get(
            '/barber/dashboard',
            BarberDashboardController::class
        )->name('barber.dashboard');

    });


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER
    |--------------------------------------------------------------------------
    |
    | role = customer
    |
    */

    Route::middleware(
        'role:' . UserRole::CUSTOMER->value
    )->group(function () {

        Route::get(
            '/customer/dashboard',
            CustomerDashboardController::class
        )->name('customer.dashboard');

    });
});


/*
|--------------------------------------------------------------------------
| SUPER ADMIN
|--------------------------------------------------------------------------
|
| auth
| +
| role:super_admin
|
*/

Route::middleware([
    'auth',
    'role:' . UserRole::SUPER_ADMIN->value,
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {


        /*
        |--------------------------------------------------------------------------
        | Admin Home
        |--------------------------------------------------------------------------
        |
        | /admin
        |    ↓
        | /admin/dashboard
        |
        */

        Route::get(
            '/',
            function () {
                return redirect()
                    ->route('admin.dashboard');
            }
        )->name('home');


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            AdminDashboardController::class
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Salon Management
        |--------------------------------------------------------------------------
        |
        | CRUD:
        |
        | /admin/salons
        |
        */

        Route::resource(
            'salons',
            AdminSalonController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Barber Management
        |--------------------------------------------------------------------------
        |
        | Barbers belong to a Salon.
        |
        | /admin/salons/{salon}/barbers
        |
        */

        Route::resource(
            'salons.barbers',
            AdminBarberController::class
        )->except([
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Service Management
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'services',
            AdminServiceController::class
        );


        /*
        |--------------------------------------------------------------------------
        | User Management
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'users',
            AdminUserController::class
        );

    });
