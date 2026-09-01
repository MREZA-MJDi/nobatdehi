<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginVerifyRequest;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | Send OTP
    |--------------------------------------------------------------------------
    */

    public function store(
        LoginRequest $request,
        OtpService $otp
    ): RedirectResponse {
        $phone = $request->validated('phone');


        /*
        |--------------------------------------------------------------------------
        | User must exist
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
        | Store pending authentication
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
        \Illuminate\Http\Request $request
    ): View|RedirectResponse {
        $pending =
            $request->session()->get(
                'auth.otp'
            );


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
            !$pending ||
            ($pending['purpose'] ?? null) !== 'login' ||
            empty($pending['phone'])
        ) {
            return redirect()
                ->route('login');
        }


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
        | Mark phone verified
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


        $request->session()->regenerate();

        $request->session()->forget(
            'auth.otp'
        );


        return redirect()
            ->intended(url('/'))
            ->with(
                'success',
                'خوش آمدید.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Resend
    |--------------------------------------------------------------------------
    */

    public function resend(
        \Illuminate\Http\Request $request,
        OtpService $otp
    ): RedirectResponse {
        $pending =
            $request->session()->get(
                'auth.otp'
            );


        if (
            !$pending ||
            ($pending['purpose'] ?? null) !== 'login'
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
