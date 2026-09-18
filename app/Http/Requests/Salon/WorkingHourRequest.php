<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'hours' => [
                'required',
                'array',
                'size:7',
            ],

            'hours.*.day_of_week' => [
                'required',
                'integer',
                Rule::in(range(0, 6)),
            ],

            'hours.*.is_closed' => [
                'required',
                'boolean',
            ],

            'hours.*.intervals' => [
                'nullable',
                'array',
            ],

            'hours.*.intervals.*.start_time' => [
                'required',
                'date_format:H:i',
            ],

            'hours.*.intervals.*.end_time' => [
                'required',
                'date_format:H:i',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hours = $this->input('hours', []);

            $days = [];

            foreach ($hours as $dayIndex => $day) {
                $dayOfWeek = $day['day_of_week'] ?? null;

                if ($dayOfWeek !== null) {
                    $dayOfWeek = (int) $dayOfWeek;

                    if (in_array($dayOfWeek, $days, true)) {
                        $validator->errors()->add(
                            "hours.$dayIndex.day_of_week",
                            'هر روز هفته فقط یک بار باید ثبت شود.'
                        );
                    }

                    $days[] = $dayOfWeek;
                }

                $isClosed = filter_var(
                    $day['is_closed'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $intervals = $day['intervals'] ?? [];

                if (!is_array($intervals)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Closed day
                |--------------------------------------------------------------------------
                */

                if ($isClosed) {
                    if (count($intervals) > 0) {
                        $validator->errors()->add(
                            "hours.$dayIndex.intervals",
                            'برای روز تعطیل نباید ساعت کاری ثبت شود.'
                        );
                    }

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Open day must have at least one interval
                |--------------------------------------------------------------------------
                */

                if (count($intervals) === 0) {
                    $validator->errors()->add(
                        "hours.$dayIndex.intervals",
                        'برای روز فعال حداقل یک بازه کاری وارد کنید.'
                    );

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Validate intervals
                |--------------------------------------------------------------------------
                */

                $normalizedIntervals = [];

                foreach ($intervals as $intervalIndex => $interval) {
                    $start = $interval['start_time'] ?? null;
                    $end = $interval['end_time'] ?? null;

                    if (!$start || !$end) {
                        continue;
                    }

                    if ($start >= $end) {
                        $validator->errors()->add(
                            "hours.$dayIndex.intervals.$intervalIndex.end_time",
                            'ساعت پایان باید بعد از ساعت شروع باشد.'
                        );

                        continue;
                    }

                    $normalizedIntervals[] = [
                        'index' => $intervalIndex,
                        'start' => $start,
                        'end' => $end,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Sort intervals by start time
                |--------------------------------------------------------------------------
                */

                usort(
                    $normalizedIntervals,
                    fn (array $a, array $b) =>
                    strcmp($a['start'], $b['start'])
                );

                /*
                |--------------------------------------------------------------------------
                | Prevent overlapping intervals
                |--------------------------------------------------------------------------
                */

                for (
                    $i = 1;
                    $i < count($normalizedIntervals);
                    $i++
                ) {
                    $previous = $normalizedIntervals[$i - 1];
                    $current = $normalizedIntervals[$i];

                    if ($current['start'] < $previous['end']) {
                        $validator->errors()->add(
                            "hours.$dayIndex.intervals.{$current['index']}.start_time",
                            'بازه‌های کاری یک روز نباید با هم تداخل داشته باشند.'
                        );
                    }
                }
            }

            sort($days);

            if ($days !== range(0, 6)) {
                $validator->errors()->add(
                    'hours',
                    'برنامه کاری باید شامل همه روزهای شنبه تا جمعه باشد.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'hours.required' =>
                'برنامه ساعات کاری الزامی است.',

            'hours.array' =>
                'فرمت ساعات کاری معتبر نیست.',

            'hours.size' =>
                'برنامه ساعات کاری باید شامل ۷ روز باشد.',

            'hours.*.day_of_week.required' =>
                'روز هفته الزامی است.',

            'hours.*.day_of_week.integer' =>
                'روز هفته معتبر نیست.',

            'hours.*.day_of_week.in' =>
                'روز هفته معتبر نیست.',

            'hours.*.is_closed.required' =>
                'وضعیت روز الزامی است.',

            'hours.*.is_closed.boolean' =>
                'وضعیت روز معتبر نیست.',

            'hours.*.intervals.array' =>
                'فرمت بازه‌های کاری معتبر نیست.',

            'hours.*.intervals.*.start_time.required' =>
                'ساعت شروع بازه الزامی است.',

            'hours.*.intervals.*.start_time.date_format' =>
                'ساعت شروع معتبر نیست.',

            'hours.*.intervals.*.end_time.required' =>
                'ساعت پایان بازه الزامی است.',

            'hours.*.intervals.*.end_time.date_format' =>
                'ساعت پایان معتبر نیست.',
        ];
    }
}
