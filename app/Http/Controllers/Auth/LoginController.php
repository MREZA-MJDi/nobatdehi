<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view('auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function store(
        LoginRequest $request
    ): RedirectResponse {
        $credentials = [
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
        ];

        $remember = $request->boolean('remember');


        /*
        |--------------------------------------------------------------------------
        | Authenticate
        |--------------------------------------------------------------------------
        */

        if (!Auth::attempt(
            $credentials,
            $remember
        )) {
            return back()
                ->withErrors([
                    'phone' =>
                        'شماره موبایل یا رمز عبور اشتباه است.',
                ])
                ->withInput(
                    $request->only([
                        'phone',
                        'remember',
                    ])
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Session Fixation
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Invalid Barber Account
        |--------------------------------------------------------------------------
        */

        if (
            $request->user()->role ===
            UserRole::BARBER
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'آرایشگر حساب ورود ندارد.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Booking Flow
        |--------------------------------------------------------------------------
        |
        | Customer started a booking before login.
        |
        */

        if (
            $request->user()->role ===
            UserRole::CUSTOMER &&
            $request->session()->has(
                'booking.pending'
            )
        ) {
            return redirect()
                ->route(
                    'customer.bookings.confirm'
                )
                ->with(
                    'success',
                    'ورود موفق بود. نوبت خود را بررسی و نهایی کنید.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Clear Invalid Booking Intent
        |--------------------------------------------------------------------------
        */

        if (
            $request->session()->has(
                'booking.pending'
            )
        ) {
            $request->session()->forget(
                'booking.pending'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Role Redirect
        |--------------------------------------------------------------------------
        */

        return match (
        $request->user()->role
        ) {

            UserRole::SUPER_ADMIN =>
            redirect()
                ->route(
                    'admin.dashboard'
                )
                ->with(
                    'success',
                    'خوش آمدید.'
                ),

            UserRole::SALON_OWNER =>
            redirect()
                ->route(
                    'salon.dashboard'
                )
                ->with(
                    'success',
                    'خوش آمدید.'
                ),

            UserRole::CUSTOMER =>
            redirect()
                ->route(
                    'customer.dashboard'
                )
                ->with(
                    'success',
                    'خوش آمدید.'
                ),

            default =>
            redirect()
                ->route('home')
                ->with(
                    'error',
                    'نقش حساب کاربری معتبر نیست.'
                ),
        };
    }
}
