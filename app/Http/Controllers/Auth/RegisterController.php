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


        /*
        |--------------------------------------------------------------------------
        | User Must Not Already Exist
        |--------------------------------------------------------------------------
        */

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
        | Store Pending Registration
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
            return back()
                ->withErrors([
                    'phone' => $e->getMessage(),
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
        $pending = $request->session()->get('auth.otp');


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
                'phone' => PhoneNumber::mask(
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
        $pending = $request->session()->get('auth.otp');


        if (
            !$pending ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone']) ||
            empty($pending['name'])
        ) {
            return redirect()
                ->route('register');
        }


        /*
        |--------------------------------------------------------------------------
        | Verify OTP
        |--------------------------------------------------------------------------
        */

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

        $user = DB::transaction(function () use ($pending) {

            /*
            |--------------------------------------------------------------------------
            | Check Again Before Create
            |--------------------------------------------------------------------------
            */

            $existing = User::query()
                ->where(
                    'phone',
                    $pending['phone']
                )
                ->first();

            if ($existing) {
                return null;
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
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Handle Race Condition
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            $request->session()->forget('auth.otp');

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'این شماره قبلاً ثبت شده است. وارد شوید.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Login Customer
        |--------------------------------------------------------------------------
        */

        Auth::login($user);


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Forget Pending OTP
        |--------------------------------------------------------------------------
        */

        $request->session()->forget('auth.otp');


        /*
        |--------------------------------------------------------------------------
        | Customer Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('customer.dashboard')
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
        $pending = $request->session()->get('auth.otp');


        if (
            !$pending ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone'])
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
