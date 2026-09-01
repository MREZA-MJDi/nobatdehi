<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SalonController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\Salon;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Salon
|--------------------------------------------------------------------------
*/

Route::get(
    '/salons/{salon:code}',
    function (Salon $salon) {

        abort_unless(
            $salon->is_active,
            404
        );

        return view(
            'customer.salon',
            compact('salon')
        );
    }
)->name('salons.show');


/*
|--------------------------------------------------------------------------
| Guest Auth
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
    )
        ->middleware('throttle:10,1')
        ->name('login.store');


    Route::get(
        '/login/verify',
        [LoginController::class, 'showVerify']
    )->name('login.verify');


    Route::post(
        '/login/verify',
        [LoginController::class, 'verify']
    )
        ->middleware('throttle:10,1')
        ->name('login.verify.store');


    Route::post(
        '/login/resend',
        [LoginController::class, 'resend']
    )
        ->middleware('throttle:5,1')
        ->name('login.resend');


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
    )
        ->middleware('throttle:10,1')
        ->name('register.store');


    Route::get(
        '/register/verify',
        [RegisterController::class, 'showVerify']
    )->name('register.verify');


    Route::post(
        '/register/verify',
        [RegisterController::class, 'verify']
    )
        ->middleware('throttle:10,1')
        ->name('register.verify.store');


    Route::post(
        '/register/resend',
        [RegisterController::class, 'resend']
    )
        ->middleware('throttle:5,1')
        ->name('register.resend');
});


/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post(
        '/logout',
        [LogoutController::class, 'store']
    )->name('logout');
});


/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:super_admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/',
            DashboardController::class
        )->name('dashboard');


        Route::resource(
            'salons',
            SalonController::class
        );
    });
