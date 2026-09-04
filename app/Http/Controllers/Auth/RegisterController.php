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
    public function create(): View
    {
        return view('auth.register');
    }


    public function store(
        RegisterRequest $request,
        OtpService $otp
    ): RedirectResponse {

        $data = $request->validated();


        $phone =
            PhoneNumber::normalize(
                $data['phone']
            );


        $request
            ->session()
            ->put(
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

                    /*
                    |--------------------------------------------------------------------------
                    | Hash before storing in session.
                    |--------------------------------------------------------------------------
                    */

                    'password_hash' =>
                        Hash::make(
                            $data['password']
                        ),

                    'created_at' =>
                        now()->timestamp,
                ]
            );


        $request->session()->save();


        try {

            $otp->send(
                $phone,
                'register',
                $request->ip()
            );

        } catch (RuntimeException $e) {

            $request
                ->session()
                ->forget(
                    'auth.otp'
                );

            return back()
                ->withErrors([
                    'phone' =>
                        $e->getMessage(),
                ])
                ->withInput();
        }


        return redirect()
            ->route(
                'register.verify'
            );
    }


    public function showVerify(
        Request $request
    ): View|RedirectResponse {

        $pending =
            $request
                ->session()
                ->get(
                    'auth.otp'
                );


        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone']) ||
            empty($pending['name']) ||
            empty($pending['password_hash'])
        ) {

            return redirect()
                ->route(
                    'register'
                )
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام پیدا نشد.',
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


    public function verify(
        RegisterVerifyRequest $request,
        OtpService $otp
    ): RedirectResponse {

        $pending =
            $request
                ->session()
                ->get(
                    'auth.otp'
                );


        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'register'
        ) {

            return redirect()
                ->route('register')
                ->withErrors([
                    'phone' =>
                        'فرآیند ثبت‌نام منقضی شده است.',
                ]);
        }


        $verified =
            $otp->verify(
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
                            $pending['password_hash'],

                        'email_verified_at' =>
                            null,
                    ]);
                }
            );


        if (
            $user->role !==
            UserRole::CUSTOMER
        ) {

            return redirect()
                ->route('login')
                ->withErrors([
                    'phone' =>
                        'این شماره متعلق به یک حساب موجود است.',
                ]);
        }


        $hasPendingBooking =
            $request
                ->session()
                ->has(
                    'booking.pending'
                );


        Auth::login(
            $user
        );


        $request
            ->session()
            ->regenerate();


        $request
            ->session()
            ->forget(
                'auth.otp'
            );


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


        return redirect()
            ->route(
                'salons.discover'
            )
            ->with(
                'success',
                'حساب شما با موفقیت ساخته شد.'
            );
    }


    public function resend(
        Request $request,
        OtpService $otp
    ): RedirectResponse {

        $pending =
            $request
                ->session()
                ->get(
                    'auth.otp'
                );


        if (
            !is_array($pending) ||
            ($pending['purpose'] ?? null) !== 'register' ||
            empty($pending['phone'])
        ) {

            return redirect()
                ->route(
                    'register'
                );
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


        return back()->with(
            'status',
            'کد جدید ارسال شد.'
        );
    }
}
