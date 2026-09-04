<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RegisterVerifyRequest;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Page
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'auth.register'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Registration OTP
    |--------------------------------------------------------------------------
    */

    public function store(
        RegisterRequest $request,
        OtpService $otp
    ): RedirectResponse {

        $data =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Normalize Phone Again
        |--------------------------------------------------------------------------
        |
        | RegisterRequest already normalizes the phone.
        | We normalize again here so session state and OTP state are identical.
        |
        */

        $phone =
            PhoneNumber::normalize(
                $data['phone']
            );


        /*
        |--------------------------------------------------------------------------
        | Store Pending Registration
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'auth.otp',
            [
                'purpose' =>
                    'register',

                'phone' =>
                    $phone,

                'name' =>
                    trim(
                        $data['name']
                    ),

                'created_at' =>
                    now()->timestamp,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | Force the session to be persisted before redirect.
        |--------------------------------------------------------------------------
        */

        $request->session()->save();


        /*
        |--------------------------------------------------------------------------
        | Send OTP
        |--------------------------------------------------------------------------
        */

        try {

            $otp->send(
                $phone,
                'register',
                $request->ip()
            );

        } catch (RuntimeException $e) {

            $request->session()->forget(
                'auth.otp'
            );

            $request->session()->save();


            return back()
                ->withErrors([
                    'phone' =>
                        $e->getMessage(),
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Go To Verification
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'register.verify'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Verification Page
    |--------------------------------------------------------------------------
    */

    public function showVerify(
        Request $request
    ): View|RedirectResponse {

        $pending =
            $request->session()->get(
                'auth.otp'
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Pending State
        |--------------------------------------------------------------------------
        */

        if (
            !is_array($pending) ||

            ($pending['purpose'] ?? null)
            !== 'register' ||

            empty(
            $pending['phone']
            ) ||

            empty(
            $pending['name']
            )
        ) {

            return redirect()
                ->route(
                    'register'
                )
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام پیدا نشد. دوباره شماره موبایل را وارد کنید.',
                ]);
        }


        return view(
            'auth.register-verify',
            [
                'phone' =>
                    PhoneNumber::mask(
                        $pending['phone']
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Registration OTP
    |--------------------------------------------------------------------------
    */

    public function verify(
        RegisterVerifyRequest $request,
        OtpService $otp
    ): RedirectResponse {

        $pending =
            $request->session()->get(
                'auth.otp'
            );


        /*
        |--------------------------------------------------------------------------
        | Pending Registration
        |--------------------------------------------------------------------------
        */

        if (
            !is_array($pending) ||

            ($pending['purpose'] ?? null)
            !== 'register' ||

            empty(
            $pending['phone']
            ) ||

            empty(
            $pending['name']
            )
        ) {

            return redirect()
                ->route(
                    'register'
                )
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام منقضی شده است. دوباره تلاش کنید.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verify OTP
        |--------------------------------------------------------------------------
        */

        $verified =
            $otp->verify(
                $pending['phone'],
                'register',
                $request->validated(
                    'code'
                )
            );


        if (!$verified) {

            return back()
                ->withErrors([
                    'code' =>
                        'کد تأیید نادرست یا منقضی شده است.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Customer
        |--------------------------------------------------------------------------
        */

        $user =
            DB::transaction(
                function () use (
                    $pending
                ) {

                    $existing =
                        User::query()
                            ->where(
                                'phone',
                                $pending['phone']
                            )
                            ->lockForUpdate()
                            ->first();


                    if ($existing) {

                        return $existing;
                    }


                    return User::create([
                        'name' =>
                            $pending['name'],

                        'phone' =>
                            $pending['phone'],

                        'phone_verified_at' =>
                            now(),

                        'role' =>
                            UserRole::CUSTOMER,

                        'password' =>
                            null,

                        'email_verified_at' =>
                            null,
                    ]);
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Existing Non-Customer Protection
        |--------------------------------------------------------------------------
        */

        if (
            $user->role !==
            UserRole::CUSTOMER
        ) {

            return redirect()
                ->route(
                    'login'
                )
                ->withErrors([
                    'phone' =>
                        'این شماره متعلق به یک حساب موجود است. وارد شوید.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Keep Booking Flow Before Session Regeneration
        |--------------------------------------------------------------------------
        */

        $hasPendingBooking =
            $request->session()->has(
                'booking.pending'
            );


        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session ID
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Remove Registration State
        |--------------------------------------------------------------------------
        */

        $request->session()->forget(
            'auth.otp'
        );


        /*
        |--------------------------------------------------------------------------
        | Continue Booking
        |--------------------------------------------------------------------------
        */

        if (
            $hasPendingBooking
        ) {

            return redirect()
                ->route(
                    'customer.bookings.confirm'
                )
                ->with(
                    'success',
                    'ثبت‌نام با موفقیت انجام شد. نوبت خود را بررسی و نهایی کنید.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'salons.discover'
            )
            ->with(
                'success',
                'حساب شما با موفقیت ساخته شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Resend OTP
    |--------------------------------------------------------------------------
    */

    public function resend(
        Request $request,
        OtpService $otp
    ): RedirectResponse {

        $pending =
            $request->session()->get(
                'auth.otp'
            );


        if (
            !is_array($pending) ||

            ($pending['purpose'] ?? null)
            !== 'register' ||

            empty(
            $pending['phone']
            )
        ) {

            return redirect()
                ->route(
                    'register'
                )
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام پیدا نشد. دوباره شماره موبایل را وارد کنید.',
                ]);
        }


        try {

            $otp->send(
                $pending['phone'],
                'register',
                $request->ip()
            );

        } catch (RuntimeException $e) {

            return back()
                ->withErrors([
                    'code' =>
                        $e->getMessage(),
                ]);
        }


        return back()
            ->with(
                'status',
                'کد جدید ارسال شد.'
            );
    }
}
