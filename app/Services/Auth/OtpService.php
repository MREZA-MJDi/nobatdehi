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

        /*
        |--------------------------------------------------------------------------
        | Normalize Phone
        |--------------------------------------------------------------------------
        */

        $phone = $this->normalizePhone($phone);


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
        | Invalidate Previous OTP
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
            ->whereNull(
                'consumed_at'
            )
            ->update([
                'consumed_at' => now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | Generate OTP
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
        */

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
        | Rate Limit
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
        | Message
        |--------------------------------------------------------------------------
        */

        $message = match ($purpose) {

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

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Roll Back Saved OTP If SMS Failed
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
                    'code',
                    $code
                )
                ->whereNull(
                    'consumed_at'
                )
                ->update([
                    'consumed_at' => now(),
                ]);


            RateLimiter::clear(
                $cooldownKey
            );


            throw new RuntimeException(
                'ارسال پیامک انجام نشد. لطفاً دوباره تلاش کنید.'
            );
        }
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
            $this->normalizePhone($phone);


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
        | Maximum Attempts
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
        | Normalize Code
        |--------------------------------------------------------------------------
        */

        $code =
            $this->normalizeDigits(
                $code
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Exact 6 Digits
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^\d{6}$/',
                $code
            )
        ) {

            $otp->increment(
                'attempts'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Verify
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
        | Clear Send Limit
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            "otp:send:{$purpose}:phone:{$phone}"
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
        | Convert Iranian +98 format to 09...
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
    | Normalize Persian / Arabic Digits
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
