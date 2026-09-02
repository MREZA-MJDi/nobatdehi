<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginVerifyRequest;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

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
    | Send Login OTP
    |--------------------------------------------------------------------------
    */

    public function store(
        LoginRequest $request,
        OtpService $otp
    ): RedirectResponse {
        $phone = $request->validated('phone');

        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'phone' =>
                        'برای این شماره حسابی پیدا نشد. ابتدا ثبت‌نام کنید.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Barber Is Not A Login Account
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === UserRole::BARBER
        ) {
            return back()
                ->withErrors([
                    'phone' =>
                        'آرایشگر حساب ورود ندارد.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Store Pending Authentication
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'auth.otp',
            [
                'purpose' => 'login',

                'phone' => $phone,

                'remember' =>
                    $request->boolean('remember'),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Force Session Save
        |--------------------------------------------------------------------------
        |
        | Make sure the OTP state is persisted before redirecting
        | to the verification page.
        |
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
                'login',
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


        return redirect()
            ->route(
                'login.verify'
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


        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'login' ||
            empty($pending['phone'])
        ) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'فرآیند ورود منقضی شده است. دوباره تلاش کنید.',
                ]);
        }


        return view(
            'auth.login-verify',
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
    | Verify OTP
    |--------------------------------------------------------------------------
    */

    public function verify(
        LoginVerifyRequest $request,
        OtpService $otp
    ): RedirectResponse {
        $pending =
            $request->session()->get(
                'auth.otp'
            );


        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'login' ||
            empty($pending['phone'])
        ) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'فرآیند ورود منقضی شده است. دوباره تلاش کنید.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verify OTP
        |--------------------------------------------------------------------------
        */

        $verified = $otp->verify(
            $pending['phone'],
            'login',
            $request->validated('code')
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
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where(
                'phone',
                $pending['phone']
            )
            ->first();


        if (!$user) {

            $request->session()->forget(
                'auth.otp'
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'حساب کاربری پیدا نشد.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Barber Protection
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === UserRole::BARBER
        ) {

            $request->session()->forget(
                'auth.otp'
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'آرایشگر حساب ورود ندارد.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Mark Phone Verified
        |--------------------------------------------------------------------------
        */

        if (
            !$user->phone_verified_at
        ) {
            $user->forceFill([
                'phone_verified_at' =>
                    now(),
            ])->save();
        }


        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            (bool) (
                $pending['remember'] ?? false
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Clear OTP State
        |--------------------------------------------------------------------------
        */

        $request->session()->forget(
            'auth.otp'
        );


        /*
        |--------------------------------------------------------------------------
        | Customer Booking Flow
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === UserRole::CUSTOMER &&
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
                    'ورود با موفقیت انجام شد. نوبت خود را بررسی و نهایی کنید.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Invalid Booking State
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

        return match ($user->role) {

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
            ($pending['purpose'] ?? null) !== 'login' ||
            empty($pending['phone'])
        ) {
            return redirect()
                ->route('login');
        }


        try {

            $otp->send(
                $pending['phone'],
                'login',
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
