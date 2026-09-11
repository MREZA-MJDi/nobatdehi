<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;

class PasswordResetController extends Controller
{
    private const PURPOSE = 'password_reset';

    public function create(): View
    {
        return view('auth.password-forgot');
    }

    public function sendOtp(
        ForgotPasswordRequest $request,
        OtpService $otp
    ): RedirectResponse {
        $phone = $request->validated('phone');

        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'phone' => 'حسابی با این شماره موبایل پیدا نشد.',
                ])
                ->withInput();
        }

        try {
            $otp->send(
                $phone,
                self::PURPOSE,
                $request->ip()
            );
        } catch (RuntimeException $e) {
            return back()
                ->withErrors([
                    'phone' => $e->getMessage(),
                ])
                ->withInput();
        }

        session([
            'auth.password_reset' => [
                'phone' => $phone,
            ],
        ]);

        return redirect()
            ->route('password.reset')
            ->with('status', 'کد تأیید برای شماره موبایل شما ارسال شد.');
    }

    public function showReset(): View|RedirectResponse
    {
        $pending = session('auth.password_reset');

        if (!is_array($pending) || empty($pending['phone'])) {
            return redirect()
                ->route('password.request');
        }

        return view('auth.password-reset', [
            'maskedPhone' => $this->maskPhone($pending['phone']),
        ]);
    }

    public function reset(
        ResetPasswordRequest $request,
        OtpService $otp
    ): RedirectResponse {
        $pending = session('auth.password_reset');

        if (!is_array($pending) || empty($pending['phone'])) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'phone' => 'درخواست تغییر رمز منقضی شده است.',
                ]);
        }

        $phone = $pending['phone'];

        $verified = $otp->verify(
            $phone,
            self::PURPOSE,
            $request->validated('code')
        );

        if (!$verified) {
            return back()
                ->withErrors([
                    'code' => 'کد تأیید اشتباه یا منقضی شده است.',
                ])
                ->withInput();
        }

        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (!$user) {
            session()->forget('auth.password_reset');

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'phone' => 'حساب کاربری پیدا نشد.',
                ]);
        }

        $user->update([
            'password' => $request->validated('password'),
        ]);

        session()->forget('auth.password_reset');

        return redirect()
            ->route('login')
            ->with('status', 'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.');
    }

    public function resend(
        OtpService $otp
    ): RedirectResponse {
        $pending = session('auth.password_reset');

        if (!is_array($pending) || empty($pending['phone'])) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'phone' => 'درخواست تغییر رمز پیدا نشد.',
                ]);
        }

        try {
            $otp->send(
                $pending['phone'],
                self::PURPOSE,
                request()->ip()
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'code' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'کد تأیید جدید ارسال شد.');
    }

    private function maskPhone(string $phone): string
    {
        $phone = PhoneNumber::normalize($phone);

        return substr($phone, 0, 4)
            . '***'
            . substr($phone, -4);
    }
}
