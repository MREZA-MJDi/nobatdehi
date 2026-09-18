<?php

use Carbon\Carbon;

if (! function_exists('jalali_date')) {

    function jalali_date($date): string
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize date
        |--------------------------------------------------------------------------
        */

        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        } else {
            $date = trim((string) $date);

            if ($date === '') {
                return '';
            }

            // اگر datetime باشد، فقط تاریخ را بردار
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
                $date = substr($date, 0, 10);
            } elseif (str_contains($date, '/')) {
                $date = str_replace('/', '-', $date);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Parse Gregorian date
        |--------------------------------------------------------------------------
        */

        $parts = explode('-', $date);

        if (count($parts) !== 3) {
            return $date;
        }

        [$gy, $gm, $gd] = array_map(
            'intval',
            $parts
        );

        if (
            $gy < 1000 ||
            $gm < 1 ||
            $gm > 12 ||
            $gd < 1 ||
            $gd > 31
        ) {
            return $date;
        }

        /*
        |--------------------------------------------------------------------------
        | Gregorian -> Jalali
        |--------------------------------------------------------------------------
        */

        $gDaysInMonth = [
            31, 28, 31, 30, 31, 30,
            31, 31, 30, 31, 30, 31
        ];

        $jDaysInMonth = [
            31, 31, 31, 31, 31, 31,
            30, 30, 30, 30, 30, 29
        ];

        $gy -= 1600;
        $gm -= 1;
        $gd -= 1;

        $gDayNo =
            365 * $gy
            + intdiv($gy + 3, 4)
            - intdiv($gy + 99, 100)
            + intdiv($gy + 399, 400);

        for ($i = 0; $i < $gm; ++$i) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if (
            $gm > 1 &&
            (
                ($gy % 4 === 0 && $gy % 100 !== 0)
                || ($gy % 400 === 0)
            )
        ) {
            $gDayNo++;
        }

        $gDayNo += $gd;

        $jDayNo = $gDayNo - 79;

        $jNp = intdiv($jDayNo, 12053);
        $jDayNo %= 12053;

        $jy = 979 + (33 * $jNp) + (4 * intdiv($jDayNo, 1461));

        $jDayNo %= 1461;

        if ($jDayNo >= 366) {
            $jy += intdiv($jDayNo - 1, 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }

        for (
            $i = 0;
            $i < 11 && $jDayNo >= $jDaysInMonth[$i];
            ++$i
        ) {
            $jDayNo -= $jDaysInMonth[$i];
        }

        $jm = $i + 1;
        $jd = $jDayNo + 1;

        return sprintf(
            '%04d/%02d/%02d',
            $jy,
            $jm,
            $jd
        );
    }
}
