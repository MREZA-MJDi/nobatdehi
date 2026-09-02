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

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

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
        | Store Pending Authentication
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'auth.otp',
            [
                'purpose' => 'login',
                'phone' => $phone,
                'remember' => $request->boolean('remember'),
            ]
        );

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
            return back()
                ->withErrors([
                    'phone' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('login.verify');
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Page
    |--------------------------------------------------------------------------
    */

    public function showVerify(
        Request $request
    ): View|RedirectResponse {
        $pending = $request->session()->get('auth.otp');

        if (
            !$pending ||
            ($pending['purpose'] ?? null) !== 'login' ||
            empty($pending['phone'])
        ) {
            return redirect()
                ->route('login');
        }

        return view(
            'auth.login-verify',
            [
                'phone' => PhoneNumber::mask(
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
        $pending = $request->session()->get('auth.otp');

        /*
        |--------------------------------------------------------------------------
        | Pending Login Check
        |--------------------------------------------------------------------------
        */

        if (
            !$pending ||
            ($pending['purpose'] ?? null) !== 'login' ||
            empty($pending['phone'])
        ) {
            return redirect()
                ->route('login');
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
        | Find User Again
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where(
                'phone',
                $pending['phone']
            )
            ->first();

        if (!$user) {
            $request->session()->forget('auth.otp');

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'حساب کاربری پیدا نشد.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Phone Verified
        |--------------------------------------------------------------------------
        */

        if (!$user->phone_verified_at) {
            $user->forceFill([
                'phone_verified_at' => now(),
            ])->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            (bool) ($pending['remember'] ?? false)
        );

        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Remove Pending OTP
        |--------------------------------------------------------------------------
        */

        $request->session()->forget('auth.otp');

        /*
        |--------------------------------------------------------------------------
        | Role Based Redirect
        |--------------------------------------------------------------------------
        */

        $redirectUrl = match ($user->role) {

            UserRole::SUPER_ADMIN =>
            route('admin.dashboard'),

            UserRole::BARBER =>
            route('barber.dashboard'),

            UserRole::CUSTOMER =>
            route('customer.dashboard'),

            default =>
            url('/'),
        };

        return redirect($redirectUrl)
            ->with(
                'success',
                'خوش آمدید.'
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
        $pending = $request->session()->get('auth.otp');

        if (
            !$pending ||
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
                    'code' => $e->getMessage(),
                ]);
        }

        return back()
            ->with(
                'status',
                'کد جدید ارسال شد.'
            );
    }
}
