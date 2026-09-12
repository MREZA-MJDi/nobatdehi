<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalonSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],


            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            'logo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:min_width=300,min_height=300',
            ],

            'cover' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
                'dimensions:min_width=800,min_height=300',
            ],

            'remove_logo' => [
                'nullable',
                'boolean',
            ],

            'remove_cover' => [
                'nullable',
                'boolean',
            ],


            /*
            |--------------------------------------------------------------------------
            | Colors
            |--------------------------------------------------------------------------
            */

            'primary_color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{3,8}$/',
            ],

            'secondary_color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{3,8}$/',
            ],


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:5000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Map
            |--------------------------------------------------------------------------
            */

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],


            /*
            |--------------------------------------------------------------------------
            | Working Hours
            |--------------------------------------------------------------------------
            */

            'working_hours' => [
                'nullable',
                'array',
            ],

            'working_hours.*' => [
                'array',
            ],

            'working_hours.*.*.start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'working_hours.*.*.end_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'working_hours.*.*.is_closed' => [
                'nullable',
                'boolean',
            ],
        ];
    }


    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'name.required' =>
                'نام سالن الزامی است.',

            'name.max' =>
                'نام سالن نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'description.max' =>
                'توضیحات سالن بیش از حد طولانی است.',


            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'email.email' =>
                'ایمیل واردشده معتبر نیست.',


            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            'logo.image' =>
                'فایل لوگو باید یک تصویر معتبر باشد.',

            'logo.mimes' =>
                'فرمت لوگو فقط می‌تواند JPG، JPEG، PNG یا WEBP باشد.',

            'logo.max' =>
                'حجم لوگو نباید بیشتر از ۵ مگابایت باشد.',

            'logo.dimensions' =>
                'اندازه لوگو باید حداقل ۳۰۰ در ۳۰۰ پیکسل باشد.',

            'cover.image' =>
                'فایل کاور باید یک تصویر معتبر باشد.',

            'cover.mimes' =>
                'فرمت کاور فقط می‌تواند JPG، JPEG، PNG یا WEBP باشد.',

            'cover.max' =>
                'حجم کاور نباید بیشتر از ۱۰ مگابایت باشد.',

            'cover.dimensions' =>
                'اندازه کاور باید حداقل ۸۰۰ در ۳۰۰ پیکسل باشد.',

            'remove_logo.boolean' =>
                'مقدار حذف لوگو معتبر نیست.',

            'remove_cover.boolean' =>
                'مقدار حذف کاور معتبر نیست.',


            /*
            |--------------------------------------------------------------------------
            | Colors
            |--------------------------------------------------------------------------
            */

            'primary_color.regex' =>
                'رنگ اصلی معتبر نیست.',

            'secondary_color.regex' =>
                'رنگ دوم معتبر نیست.',


            /*
            |--------------------------------------------------------------------------
            | Map
            |--------------------------------------------------------------------------
            */

            'latitude.numeric' =>
                'عرض جغرافیایی معتبر نیست.',

            'latitude.between' =>
                'عرض جغرافیایی باید بین ۹۰- و ۹۰ باشد.',

            'longitude.numeric' =>
                'طول جغرافیایی معتبر نیست.',

            'longitude.between' =>
                'طول جغرافیایی باید بین ۱۸۰- و ۱۸۰ باشد.',


            /*
            |--------------------------------------------------------------------------
            | Working Hours
            |--------------------------------------------------------------------------
            */

            'working_hours.array' =>
                'ساختار ساعات کاری معتبر نیست.',

            'working_hours.*.array' =>
                'ساختار روزهای کاری معتبر نیست.',

            'working_hours.*.*.start_time.date_format' =>
                'ساعت شروع باید با فرمت HH:MM باشد.',

            'working_hours.*.*.end_time.date_format' =>
                'ساعت پایان باید با فرمت HH:MM باشد.',

            'working_hours.*.*.is_closed.boolean' =>
                'وضعیت روز کاری معتبر نیست.',
        ];
    }


    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |--------------------------------------------------------------------------
            | Working Hours
            |--------------------------------------------------------------------------
            */

            $workingHours = $this->input(
                'working_hours',
                []
            );

            if (!is_array($workingHours)) {
                return;
            }

            foreach (
                $workingHours as $day => $intervals
            ) {

                if (!is_array($intervals)) {
                    continue;
                }

                foreach (
                    $intervals as $sortOrder => $interval
                ) {

                    if (!is_array($interval)) {
                        continue;
                    }

                    $isClosed = !empty(
                    $interval['is_closed']
                    );

                    if ($isClosed) {
                        continue;
                    }

                    $start =
                        $interval['start_time']
                        ?? null;

                    $end =
                        $interval['end_time']
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | Both or None
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ($start && !$end) ||
                        (!$start && $end)
                    ) {

                        $validator->errors()->add(
                            "working_hours.$day.$sortOrder.start_time",
                            'برای بازه کاری، ساعت شروع و پایان را کامل وارد کنید.'
                        );

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | End Must Be After Start
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $start &&
                        $end &&
                        $start >= $end
                    ) {

                        $validator->errors()->add(
                            "working_hours.$day.$sortOrder.end_time",
                            'ساعت پایان باید بعد از ساعت شروع باشد.'
                        );
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Logo / Cover State
            |--------------------------------------------------------------------------
            |
            | A remove flag and a new file can exist together.
            | The Controller already gives the new file priority.
            |
            | Therefore we intentionally do not reject this combination.
            |
            |--------------------------------------------------------------------------
            */


            /*
            |--------------------------------------------------------------------------
            | Brand Colors
            |--------------------------------------------------------------------------
            */

            foreach ([
                         'primary_color',
                         'secondary_color',
                     ] as $field) {

                $value = $this->input($field);

                if (
                    $value !== null &&
                    $value !== '' &&
                    !preg_match(
                        '/^#[0-9A-Fa-f]{3,8}$/',
                        (string) $value
                    )
                ) {
                    $validator->errors()->add(
                        $field,
                        'کد رنگ واردشده معتبر نیست.'
                    );
                }
            }
        });
    }
}
