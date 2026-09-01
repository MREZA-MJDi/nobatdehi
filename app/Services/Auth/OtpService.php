<?php

namespace App\Services\Auth;

use App\Contracts\SmsSender;
use App\Models\PhoneOtp;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class OtpService
{
    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */

    private const CODE_LENGTH = 6;

    private const EXPIRE_MINUTES = 2;

    private const MAX_ATTEMPTS = 5;

    private const RESEND_SECONDS = 60;

    private const PHONE_SEND_LIMIT = 5;

    private const IP_SEND_LIMIT = 20;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly SmsSender $sms
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Send OTP
    |--------------------------------------------------------------------------
    */

    public function send(
        string $phone,
        string $purpose,
        ?string $ip = null
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Rate Limit - Phone
        |--------------------------------------------------------------------------
        */

        $phoneKey =
            "otp:send:{$purpose}:phone:{$phone}";

        if (
            RateLimiter::tooManyAttempts(
                $phoneKey,
                self::PHONE_SEND_LIMIT
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $phoneKey
                );

            throw new RuntimeException(
                "تعداد درخواست‌ها بیش از حد مجاز است. {$seconds} ثانیه دیگر دوباره تلاش کنید."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Rate Limit - IP
        |--------------------------------------------------------------------------
        */

        $ipKey = null;

        if ($ip) {

            $ipKey =
                "otp:send:{$purpose}:ip:{$ip}";

            if (
                RateLimiter::tooManyAttempts(
                    $ipKey,
                    self::IP_SEND_LIMIT
                )
            ) {
                throw new RuntimeException(
                    'تعداد درخواست‌ها از این IP بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Resend Cooldown
        |--------------------------------------------------------------------------
        */

        $cooldownKey =
            "otp:cooldown:{$purpose}:{$phone}";

        if (
            RateLimiter::tooManyAttempts(
                $cooldownKey,
                1
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $cooldownKey
                );

            throw new RuntimeException(
                "ارسال مجدد تا {$seconds} ثانیه دیگر امکان‌پذیر نیست."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invalidate Previous OTPs
        |--------------------------------------------------------------------------
        */

        PhoneOtp::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update([
                'consumed_at' => now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | Generate 6 Digit OTP
        |--------------------------------------------------------------------------
        */

        $code = str_pad(
            (string) random_int(0, 999999),
            self::CODE_LENGTH,
            '0',
            STR_PAD_LEFT
        );


        /*
        |--------------------------------------------------------------------------
        | Save OTP
        |--------------------------------------------------------------------------
        |
        | Development version:
        | code is stored directly so we can inspect it easily.
        |
        | Before production we should switch this to a hashed value.
        |
        */

        PhoneOtp::create([
            'phone' => $phone,

            'purpose' => $purpose,

            'code' => $code,

            'attempts' => 0,

            'expires_at' => now()->addMinutes(
                self::EXPIRE_MINUTES
            ),

            'sent_at' => now(),

            'ip_address' => $ip,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Mark Rate Limits
        |--------------------------------------------------------------------------
        */

        RateLimiter::hit(
            $phoneKey,
            600
        );


        if ($ipKey) {
            RateLimiter::hit(
                $ipKey,
                600
            );
        }


        RateLimiter::hit(
            $cooldownKey,
            self::RESEND_SECONDS
        );


        /*
        |--------------------------------------------------------------------------
        | Build SMS Message
        |--------------------------------------------------------------------------
        */

        $message = match ($purpose) {

            'login' =>
            "کد ورود شما: {$code}\nاین کد تا ۲ دقیقه معتبر است.",

            'register' =>
            "کد ثبت‌نام نوبت‌دهی: {$code}\nاین کد تا ۲ دقیقه معتبر است.",

            default =>
            "کد تأیید شما: {$code}\nاین کد تا ۲ دقیقه معتبر است.",
        };


        /*
        |--------------------------------------------------------------------------
        | Send SMS
        |--------------------------------------------------------------------------
        */

        $this->sms->send(
            $phone,
            $message
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify OTP
    |--------------------------------------------------------------------------
    */

    public function verify(
        string $phone,
        string $purpose,
        string $code
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Get Latest Active OTP
        |--------------------------------------------------------------------------
        */

        $otp = PhoneOtp::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | No OTP Found
        |--------------------------------------------------------------------------
        */

        if (!$otp) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Expired
        |--------------------------------------------------------------------------
        */

        if (
            now()->greaterThan(
                $otp->expires_at
            )
        ) {
            $otp->update([
                'consumed_at' => now(),
            ]);

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Maximum Attempts
        |--------------------------------------------------------------------------
        */

        if (
            $otp->attempts >=
            self::MAX_ATTEMPTS
        ) {
            $otp->update([
                'consumed_at' => now(),
            ]);

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Incoming Code
        |--------------------------------------------------------------------------
        */

        $code = preg_replace(
            '/\D/',
            '',
            $code
        );


        /*
        |--------------------------------------------------------------------------
        | Verify
        |--------------------------------------------------------------------------
        */

        if (
            !is_string($code) ||
            !hash_equals(
                (string) $otp->code,
                $code
            )
        ) {

            $otp->increment(
                'attempts'
            );


            /*
            |--------------------------------------------------------------------------
            | Consume after too many attempts
            |--------------------------------------------------------------------------
            */

            if (
                $otp->attempts >=
                self::MAX_ATTEMPTS
            ) {
                $otp->update([
                    'consumed_at' => now(),
                ]);
            }


            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $otp->update([
            'consumed_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Clear Phone Rate Limit
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            "otp:send:{$purpose}:phone:{$phone}"
        );


        /*
        |--------------------------------------------------------------------------
        | Return Success
        |--------------------------------------------------------------------------
        */

        return true;
    }
}
