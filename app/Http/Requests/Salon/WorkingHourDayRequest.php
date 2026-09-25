<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkingHourDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        $salonId = $this->user()?->managedSalons()->value('id');

        return [
            'barber_id' => [
                'nullable',
                'integer',
                Rule::exists('barbers', 'id')->where(
                    fn ($query) => $query->where('salon_id', $salonId)
                ),
            ],
            'day_of_week' => [
                'required',
                'integer',
                Rule::in(range(0, 6)),
            ],
            'is_closed' => [
                'required',
                'boolean',
            ],
            'intervals' => [
                'nullable',
                'array',
            ],
            'intervals.*.start_time' => [
                'required',
                'date_format:H:i',
            ],
            'intervals.*.end_time' => [
                'required',
                'date_format:H:i',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $isClosed = filter_var(
                $this->input('is_closed', false),
                FILTER_VALIDATE_BOOLEAN
            );

            $intervals = $this->input('intervals', []);

            if (!is_array($intervals)) {
                return;
            }

            if ($isClosed) {
                if (count($intervals) > 0) {
                    $validator->errors()->add(
                        'intervals',
                        'برای روز تعطیل نباید بازه کاری ثبت شود.'
                    );
                }

                return;
            }

            if (count($intervals) === 0) {
                $validator->errors()->add(
                    'intervals',
                    'برای روز فعال حداقل یک بازه کاری وارد کنید.'
                );

                return;
            }

            $normalized = [];

            foreach ($intervals as $index => $interval) {
                $start = $interval['start_time'] ?? null;
                $end = $interval['end_time'] ?? null;

                if (!$start || !$end) {
                    continue;
                }

                if ($start >= $end) {
                    $validator->errors()->add(
                        "intervals.$index.end_time",
                        'ساعت پایان باید بعد از ساعت شروع باشد.'
                    );

                    continue;
                }

                $normalized[] = [
                    'index' => $index,
                    'start' => $start,
                    'end' => $end,
                ];
            }

            usort(
                $normalized,
                fn (array $a, array $b) => strcmp($a['start'], $b['start'])
            );

            for ($index = 1; $index < count($normalized); $index++) {
                $previous = $normalized[$index - 1];
                $current = $normalized[$index];

                if ($current['start'] < $previous['end']) {
                    $validator->errors()->add(
                        "intervals.{$current['index']}.start_time",
                        'بازه‌های کاری یک روز نباید با هم تداخل داشته باشند.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'barber_id.integer' => 'آرایشگر انتخاب‌شده معتبر نیست.',
            'barber_id.exists' => 'آرایشگر انتخاب‌شده به این سالن تعلق ندارد.',
            'day_of_week.required' => 'روز هفته الزامی است.',
            'day_of_week.integer' => 'روز هفته معتبر نیست.',
            'day_of_week.in' => 'روز هفته معتبر نیست.',
            'is_closed.required' => 'وضعیت روز الزامی است.',
            'is_closed.boolean' => 'وضعیت روز معتبر نیست.',
            'intervals.array' => 'فرمت بازه‌های کاری معتبر نیست.',
            'intervals.*.start_time.required' => 'ساعت شروع بازه الزامی است.',
            'intervals.*.start_time.date_format' => 'ساعت شروع معتبر نیست.',
            'intervals.*.end_time.required' => 'ساعت پایان بازه الزامی است.',
            'intervals.*.end_time.date_format' => 'ساعت پایان معتبر نیست.',
        ];
    }
}
