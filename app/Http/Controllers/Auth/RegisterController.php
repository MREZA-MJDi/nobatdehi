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
use Illuminate\Support\Facades\Hash;
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
    | Start Registration
    |--------------------------------------------------------------------------
    */

    public function store(
        RegisterRequest $request,
        OtpService $otp
    ): RedirectResponse {
        $data = $request->validated();

        $phone = PhoneNumber::normalize(
            $data['phone']
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent Registering an Existing Phone
        |--------------------------------------------------------------------------
        */

        $existing = User::query()
            ->where('phone', $phone)
            ->first();

        if (
            $existing &&
            ! (
                $existing->isCustomer() &&
                $existing->password === null &&
                $existing->phone_verified_at === null
            )
        ) {
            return back()
                ->withErrors([
                    'phone' =>
                        'این شماره موبایل قبلاً ثبت شده است. وارد حساب خود شوید.',
                ])
                ->withInput();
        }

        if (! config('auth-flow.registration_otp_required')) {
            return $this->completeRegistrationWithoutOtp(
                $request,
                $phone,
                trim($data['name']),
                Hash::make($data['password'])
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Store Pending Registration
        |--------------------------------------------------------------------------
        |
        | Password is hashed before entering the session.
        |
        */

        $request->session()->put(
            'auth.otp',
            [
                'purpose' => 'register',

                'phone' => $phone,

                'name' => trim(
                    $data['name']
                ),

                'password_hash' => Hash::make(
                    $data['password']
                ),

                'created_at' => now()->timestamp,
            ]
        );

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
    | Local / development registration without SMS OTP
    |--------------------------------------------------------------------------
    */

    private function completeRegistrationWithoutOtp(
        Request $request,
        string $phone,
        string $name,
        string $passwordHash
    ): RedirectResponse {
        try {
            $user = DB::transaction(function () use (
                $phone,
                $name,
                $passwordHash
            ) {
                $existing = User::query()
                    ->where('phone', $phone)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if (! $existing->isCustomer()) {
                        throw new RuntimeException(
                            'این شماره موبایل متعلق به یک حساب غیرمشتری است.'
                        );
                    }

                    $existing->update([
                        'name' => $name,
                        'password' => $passwordHash,
                        'phone_verified_at' => now(),
                    ]);

                    return $existing->fresh();
                }

                return User::create([
                    'name' => $name,
                    'phone' => $phone,
                    'phone_verified_at' => now(),
                    'role' => UserRole::CUSTOMER,
                    'password' => $passwordHash,
                    'email_verified_at' => null,
                ]);
            });
        } catch (RuntimeException $e) {
            return back()
                ->withErrors([
                    'phone' => $e->getMessage(),
                ])
                ->withInput();
        }

        $hasPendingBooking = $request
            ->session()
            ->has('booking.pending');

        Auth::login($user);

        $request
            ->session()
            ->regenerate();

        $request
            ->session()
            ->forget('auth.otp');

        if ($hasPendingBooking) {
            return redirect()
                ->route('customer.bookings.confirm')
                ->with(
                    'success',
                    'ثبت‌نام موفق بود. نوبت را بررسی و نهایی کنید.'
                );
        }

        return redirect()
            ->route('salons.discover')
            ->with(
                'success',
                'حساب شما با موفقیت ساخته شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Verification Page
    |--------------------------------------------------------------------------
    */

    public function showVerify(
        Request $request
    ): View|RedirectResponse {
        $pending = $request
            ->session()
            ->get('auth.otp');

        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone']) ||
            empty($pending['name']) ||
            empty($pending['password_hash'])
        ) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام پیدا نشد.',
                ]);
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
        $pending = $request
            ->session()
            ->get('auth.otp');

        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone']) ||
            empty($pending['name']) ||
            empty($pending['password_hash'])
        ) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام منقضی شده است.',
                ]);
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
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Create Customer
        |--------------------------------------------------------------------------
        */

        try {
            $user = DB::transaction(
                function () use ($pending) {

                    $existing = User::query()
                        ->where(
                            'phone',
                            $pending['phone']
                        )
                        ->lockForUpdate()
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | The phone was registered after the OTP was requested.
                    |--------------------------------------------------------------------------
                    */

                    if ($existing) {
                        if (
                            ! $existing->isCustomer() ||
                            $existing->password !== null ||
                            $existing->phone_verified_at !== null
                        ) {
                            throw new RuntimeException(
                                'این شماره موبایل قبلاً ثبت شده است.'
                            );
                        }

                        $existing->update([
                            'name' => $pending['name'],
                            'password' => $pending['password_hash'],
                            'phone_verified_at' => now(),
                        ]);

                        return $existing->fresh();
                    }

                    return User::create([
                        'name' => $pending['name'],

                        'phone' => $pending['phone'],

                        'phone_verified_at' => now(),

                        'role' => UserRole::CUSTOMER,

                        'password' =>
                            $pending['password_hash'],

                        'email_verified_at' => null,
                    ]);
                }
            );
        } catch (RuntimeException $e) {
            $request->session()->forget(
                'auth.otp'
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' => $e->getMessage(),
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending Booking
        |--------------------------------------------------------------------------
        */

        $hasPendingBooking = $request
            ->session()
            ->has('booking.pending');

        /*
        |--------------------------------------------------------------------------
        | Authenticate
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        /*
        |--------------------------------------------------------------------------
        | Prevent Session Fixation
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Clear Registration Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget('auth.otp');

        /*
        |--------------------------------------------------------------------------
        | Continue Pending Booking
        |--------------------------------------------------------------------------
        */

        if ($hasPendingBooking) {
            return redirect()
                ->route(
                    'customer.bookings.confirm'
                )
                ->with(
                    'success',
                    'ثبت‌نام موفق بود. نوبت را بررسی و نهایی کنید.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Default Redirect
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
        $pending = $request
            ->session()
            ->get('auth.otp');

        if (
            !is_array($pending) ||
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
