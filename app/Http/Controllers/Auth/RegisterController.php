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
    public function create(): View
    {
        return view('auth.register');
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
        $data = $request->validated();


        $phone = $data['phone'];


        if (
            User::query()
                ->where('phone', $phone)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'phone' =>
                        'این شماره قبلاً ثبت شده است. وارد شوید.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Store pending registration
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'auth.otp',
            [
                'purpose' => 'register',

                'phone' => $phone,

                'name' => $data['name'],
            ]
        );


        try {

            $otp->send(
                $phone,
                'register',
                $request->ip()
            );

        } catch (RuntimeException $e) {

            return back()
                ->withErrors([
                    'phone' =>
                        $e->getMessage(),
                ])
                ->withInput();
        }


        return redirect()
            ->route('register.verify');
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Page
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
            !$pending ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone']) ||
            empty($pending['name'])
        ) {
            return redirect()
                ->route('register');
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


        if (
            !$pending ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone']) ||
            empty($pending['name'])
        ) {
            return redirect()
                ->route('register');
        }


        $verified = $otp->verify(
            $pending['phone'],
            'register',
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
        | Create Customer
        |--------------------------------------------------------------------------
        */

        $user = DB::transaction(
            function () use ($pending) {

                /*
                * Race-condition protection:
                * check phone again immediately before create.
                */

                $existing =
                    User::query()
                        ->where(
                            'phone',
                            $pending['phone']
                        )
                        ->first();

                if ($existing) {
                    return $existing;
                }


                return User::create([
                    'name' => $pending['name'],

                    'phone' => $pending['phone'],

                    'phone_verified_at' => now(),

                    'role' => UserRole::CUSTOMER,

                    'password' => null,
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        $request->session()->regenerate();


        $request->session()->forget(
            'auth.otp'
        );


        return redirect()
            ->intended(url('/'))
            ->with(
                'success',
                'حساب شما با موفقیت ساخته شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Resend
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
            !$pending ||
            ($pending['purpose'] ?? null) !== 'register'
        ) {
            return redirect()
                ->route('register');
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
