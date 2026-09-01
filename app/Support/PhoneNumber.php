<?php

namespace App\Support;

use InvalidArgumentException;

class PhoneNumber
{
    public static function normalize(string $phone): string
    {
        $phone = trim($phone);

        /*
        |--------------------------------------------------------------------------
        | Persian / Arabic digits → English digits
        |--------------------------------------------------------------------------
        */

        $phone = strtr($phone, [
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
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove spaces / separators
        |--------------------------------------------------------------------------
        */

        $phone = preg_replace(
            '/[\s\-\(\)]+/',
            '',
            $phone
        );


        /*
        |--------------------------------------------------------------------------
        | Iranian prefixes → local format
        |--------------------------------------------------------------------------
        */

        if (str_starts_with($phone, '+98')) {
            $phone = '0' . substr($phone, 3);
        }

        if (str_starts_with($phone, '0098')) {
            $phone = '0' . substr($phone, 4);
        }

        if (str_starts_with($phone, '98') && strlen($phone) === 12) {
            $phone = '0' . substr($phone, 2);
        }


        /*
        |--------------------------------------------------------------------------
        | 912... → 0912...
        |--------------------------------------------------------------------------
        */

        if (
            strlen($phone) === 10 &&
            str_starts_with($phone, '9')
        ) {
            $phone = '0' . $phone;
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        if (!preg_match('/^09\d{9}$/', $phone)) {
            throw new InvalidArgumentException(
                'شماره موبایل معتبر نیست.'
            );
        }


        return $phone;
    }


    public static function mask(
        string $phone
    ): string {
        $phone = self::normalize($phone);

        return substr($phone, 0, 4)
            . ' *** '
            . substr($phone, -4);
    }
}
