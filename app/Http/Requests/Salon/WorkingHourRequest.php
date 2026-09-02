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

            'hours.*.start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'hours.*.end_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'hours.*.is_closed' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $hours = $this->input(
                'hours',
                []
            );

            $days = [];

            foreach ($hours as $index => $hour) {

                $day = $hour['day_of_week'] ?? null;

                if ($day !== null) {
                    $day = (int) $day;

                    if (in_array($day, $days, true)) {
                        $validator->errors()->add(
                            "hours.$index.day_of_week",
                            'هر روز هفته فقط یک بار باید ثبت شود.'
                        );
                    }

                    $days[] = $day;
                }

                $isClosed = filter_var(
                    $hour['is_closed'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                /*
                |--------------------------------------------------------------------------
                | Closed Day
                |--------------------------------------------------------------------------
                */

                if ($isClosed) {
                    continue;
                }

                $start = $hour['start_time'] ?? null;
                $end = $hour['end_time'] ?? null;

                if (!$start || !$end) {
                    $validator->errors()->add(
                        "hours.$index.start_time",
                        'برای روز فعال، ساعت شروع و پایان الزامی است.'
                    );

                    continue;
                }

                if ($start >= $end) {
                    $validator->errors()->add(
                        "hours.$index.end_time",
                        'ساعت پایان باید بعد از ساعت شروع باشد.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Exactly Saturday -> Friday
            |--------------------------------------------------------------------------
            */

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

            'hours.*.start_time.date_format' =>
                'ساعت شروع معتبر نیست.',

            'hours.*.end_time.date_format' =>
                'ساعت پایان معتبر نیست.',

            'hours.*.is_closed.required' =>
                'وضعیت روز الزامی است.',

            'hours.*.is_closed.boolean' =>
                'وضعیت روز معتبر نیست.',
        ];
    }
}
