<?php

namespace App\Services\Auth;

use App\Contracts\SmsSender;
use App\Models\PhoneOtp;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class OtpService
{
    private const CODE_LENGTH = 6;

    private const EXPIRE_MINUTES = 2;

    private const MAX_ATTEMPTS = 5;

    private const RESEND_SECONDS = 60;

    private const PHONE_SEND_LIMIT = 5;

    private const IP_SEND_LIMIT = 20;


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

        $phone =
            $this->normalizePhone(
                $phone
            );


        /*
        |--------------------------------------------------------------------------
        | Keys
        |--------------------------------------------------------------------------
        */

        $phoneKey =
            "otp:send:{$purpose}:phone:{$phone}";


        $cooldownKey =
            "otp:cooldown:{$purpose}:{$phone}";


        $ipKey = null;


        if ($ip) {

            $ipKey =
                "otp:send:{$purpose}:ip:{$ip}";
        }


        /*
        |--------------------------------------------------------------------------
        | Phone Rate Limit
        |--------------------------------------------------------------------------
        */

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
        | IP Rate Limit
        |--------------------------------------------------------------------------
        */

        if (
            $ipKey &&
            RateLimiter::tooManyAttempts(
                $ipKey,
                self::IP_SEND_LIMIT
            )
        ) {

            throw new RuntimeException(
                'تعداد درخواست‌ها از این IP بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Resend Cooldown
        |--------------------------------------------------------------------------
        */

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
        | Generate OTP
        |--------------------------------------------------------------------------
        */

        $code =
            str_pad(
                (string) random_int(
                    0,
                    999999
                ),
                self::CODE_LENGTH,
                '0',
                STR_PAD_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | Save New OTP
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Previous OTP is NOT invalidated yet.
        | It will be invalidated only after SMS succeeds.
        |
        */

        $newOtp =
            PhoneOtp::create([
                'phone' =>
                    $phone,

                'purpose' =>
                    $purpose,

                'code' =>
                    $code,

                'attempts' =>
                    0,

                'expires_at' =>
                    now()->addMinutes(
                        self::EXPIRE_MINUTES
                    ),

                'sent_at' =>
                    now(),

                'ip_address' =>
                    $ip,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Message
        |--------------------------------------------------------------------------
        */

        $message =
            match ($purpose) {

                'login' =>
                "کد ورود شما: {$code}\nاین کد تا ۲ دقیقه معتبر است.",

                'register' =>
                "کد ثبت‌نام: {$code}\nاین کد تا ۲ دقیقه معتبر است.",

                default =>
                "کد تأیید شما: {$code}\nاین کد تا ۲ دقیقه معتبر است.",

            };


        /*
        |--------------------------------------------------------------------------
        | Send SMS
        |--------------------------------------------------------------------------
        */

        try {

            $this->sms->send(
                $phone,
                $message
            );

        } catch (\Throwable) {

            /*
            |--------------------------------------------------------------------------
            | Remove New OTP
            |--------------------------------------------------------------------------
            */

            $newOtp->delete();


            /*
            |--------------------------------------------------------------------------
            | Do NOT consume previous OTP
            |--------------------------------------------------------------------------
            */

            throw new RuntimeException(
                'ارسال پیامک انجام نشد. لطفاً دوباره تلاش کنید.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Success -> Invalidate Previous OTPs
        |--------------------------------------------------------------------------
        */

        PhoneOtp::query()
            ->where(
                'phone',
                $phone
            )
            ->where(
                'purpose',
                $purpose
            )
            ->where(
                'id',
                '!=',
                $newOtp->id
            )
            ->whereNull(
                'consumed_at'
            )
            ->update([
                'consumed_at' =>
                    now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | Rate Limits
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

        $phone =
            $this->normalizePhone(
                $phone
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Code
        |--------------------------------------------------------------------------
        */

        $code =
            $this->normalizeDigits(
                $code
            );


        /*
        |--------------------------------------------------------------------------
        | Exact 6 Digits
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^\d{6}$/',
                $code
            )
        ) {

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Get Latest Active OTP
        |--------------------------------------------------------------------------
        */

        $otp =
            PhoneOtp::query()
                ->where(
                    'phone',
                    $phone
                )
                ->where(
                    'purpose',
                    $purpose
                )
                ->whereNull(
                    'consumed_at'
                )
                ->latest('id')
                ->first();


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
                'consumed_at' =>
                    now(),
            ]);


            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Attempts
        |--------------------------------------------------------------------------
        */

        if (
            $otp->attempts >=
            self::MAX_ATTEMPTS
        ) {

            $otp->update([
                'consumed_at' =>
                    now(),
            ]);


            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Compare
        |--------------------------------------------------------------------------
        */

        if (
            !hash_equals(
                (string) $otp->code,
                $code
            )
        ) {

            $otp->increment(
                'attempts'
            );


            if (
                $otp->attempts >=
                self::MAX_ATTEMPTS
            ) {

                $otp->update([
                    'consumed_at' =>
                        now(),
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
            'consumed_at' =>
                now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Clear Phone Send Rate
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            "otp:send:{$purpose}:phone:{$phone}"
        );


        /*
        |--------------------------------------------------------------------------
        | Clear Cooldown
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            "otp:cooldown:{$purpose}:{$phone}"
        );


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Phone
    |--------------------------------------------------------------------------
    */

    private function normalizePhone(
        string $phone
    ): string {

        $phone =
            $this->normalizeDigits(
                $phone
            );


        $phone =
            preg_replace(
                '/[\s\-\(\)]/',
                '',
                $phone
            );


        /*
        |--------------------------------------------------------------------------
        | +98xxxxxxxxxx
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $phone,
                '+98'
            )
        ) {

            $phone =
                '0' .
                substr(
                    $phone,
                    3
                );

        } elseif (
            str_starts_with(
                $phone,
                '0098'
            )
        ) {

            $phone =
                '0' .
                substr(
                    $phone,
                    4
                );
        }


        return $phone;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Digits
    |--------------------------------------------------------------------------
    */

    private function normalizeDigits(
        string $value
    ): string {

        return strtr(
            $value,
            [

                '۰' => '0',
                '۱' => '1',
                '۲' => '2',
                '۳' => '3',
                '۴' => '4',
                '۵' => '5',
                '۶' => '6',
                '۷' => '7',
                '۸' => '8',
                '۹' => '9',

                '٠' => '0',
                '١' => '1',
                '٢' => '2',
                '٣' => '3',
                '٤' => '4',
                '٥' => '5',
                '٦' => '6',
                '٧' => '7',
                '٨' => '8',
                '٩' => '9',

            ]
        );
    }
}
