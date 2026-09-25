<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(
                UserRole::SUPER_ADMIN
            ) === true;
    }

    protected function prepareForValidation(): void
    {
        $phone = trim(
            (string) $this->input('manager_phone', '')
        );

        if ($phone !== '') {
            try {
                $phone = PhoneNumber::normalize($phone);
            } catch (\Throwable) {
                //
            }
        }

        $this->merge([
            'manager_phone' => $phone,
        ]);
    }

    public function rules(): array
    {
        $salon = $this->route('salon');

        $ownerId = $salon?->owner_id;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'slug' => [
                'required',
                'string',
                'min:2',
                'max:120',
                'alpha_dash',
                Rule::unique('salons', 'slug')
                    ->ignore($salon?->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'manager_name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'manager_phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
                Rule::unique('users', 'phone')
                    ->ignore($ownerId),
            ],

            'manager_password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'email' => [
                'nullable',
                'email',
                'max:190',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],

            'remove_logo' => [
                'nullable',
                'boolean',
            ],

            'remove_cover' => [
                'nullable',
                'boolean',
            ],

            'primary_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'secondary_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

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
                'max:1000',
            ],

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

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'services' => [
                'nullable',
                'array',
            ],
            'services.*.id' => [
                'nullable',
                'integer',
            ],
            'services.*.name' => [
                'required_without:services.*.delete',
                'nullable',
                'string',
                'max:160',
            ],
            'services.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'services.*.duration_minutes' => [
                'required_without:services.*.delete',
                'nullable',
                'integer',
                'min:5',
                'max:1440',
            ],
            'services.*.price' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'services.*.is_active' => [
                'nullable',
                'boolean',
            ],
            'services.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'services.*.delete' => [
                'nullable',
                'boolean',
            ],

            'barbers' => [
                'nullable',
                'array',
            ],
            'barbers.*.id' => [
                'nullable',
                'integer',
            ],
            'barbers.*.name' => [
                'required_without:barbers.*.delete',
                'nullable',
                'string',
                'max:160',
            ],
            'barbers.*.specialty' => [
                'nullable',
                'string',
                'max:160',
            ],
            'barbers.*.phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'barbers.*.bio' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'barbers.*.is_active' => [
                'nullable',
                'boolean',
            ],
            'barbers.*.delete' => [
                'nullable',
                'boolean',
            ],

            'working_hours' => [
                'nullable',
                'array',
            ],
            'working_hours.*.id' => [
                'nullable',
                'integer',
            ],
            'working_hours.*.day_of_week' => [
                'required',
                'integer',
                'between:0,6',
            ],
            'working_hours.*.start_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'working_hours.*.end_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'working_hours.*.is_closed' => [
                'nullable',
                'boolean',
            ],
            'working_hours.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'نام سالن الزامی است.',

            'name.min' =>
                'نام سالن حداقل باید ۲ کاراکتر باشد.',

            'name.max' =>
                'نام سالن نباید بیشتر از ۱۲۰ کاراکتر باشد.',

            'slug.required' =>
                'Slug سالن الزامی است.',

            'slug.alpha_dash' =>
                'Slug فقط باید شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',

            'slug.unique' =>
                'این Slug قبلاً برای سالن دیگری ثبت شده است.',

            'description.max' =>
                'توضیحات سالن نباید بیشتر از ۵۰۰۰ کاراکتر باشد.',

            'manager_name.required' =>
                'نام مسئول سالن الزامی است.',

            'manager_name.min' =>
                'نام مسئول سالن حداقل باید ۲ کاراکتر باشد.',

            'manager_name.max' =>
                'نام مسئول سالن نباید بیشتر از ۱۲۰ کاراکتر باشد.',

            'manager_phone.required' =>
                'شماره موبایل مسئول سالن الزامی است.',

            'manager_phone.regex' =>
                'شماره موبایل مسئول سالن معتبر نیست.',

            'manager_phone.unique' =>
                'این شماره موبایل قبلاً برای حساب دیگری ثبت شده است.',

            'manager_password.min' =>
                'رمز عبور مسئول سالن باید حداقل ۸ کاراکتر باشد.',

            'manager_password.confirmed' =>
                'تکرار رمز عبور مسئول سالن یکسان نیست.',

            'email.email' =>
                'ایمیل وارد شده معتبر نیست.',

            'email.max' =>
                'ایمیل نباید بیشتر از ۱۹۰ کاراکتر باشد.',

            'logo.image' =>
                'فایل لوگو باید تصویر باشد.',

            'logo.mimes' =>
                'فرمت لوگو باید jpg، jpeg، png یا webp باشد.',

            'logo.max' =>
                'حجم لوگو نباید بیشتر از ۴ مگابایت باشد.',

            'cover.image' =>
                'فایل Cover باید تصویر باشد.',

            'cover.mimes' =>
                'فرمت Cover باید jpg، jpeg، png یا webp باشد.',

            'cover.max' =>
                'حجم Cover نباید بیشتر از ۸ مگابایت باشد.',

            'remove_logo.boolean' =>
                'مقدار حذف لوگو معتبر نیست.',

            'remove_cover.boolean' =>
                'مقدار حذف Cover معتبر نیست.',

            'primary_color.regex' =>
                'رنگ اصلی معتبر نیست.',

            'secondary_color.regex' =>
                'رنگ مکمل معتبر نیست.',

            'province.max' =>
                'نام استان نباید بیشتر از ۱۰۰ کاراکتر باشد.',

            'city.max' =>
                'نام شهر نباید بیشتر از ۱۰۰ کاراکتر باشد.',

            'district.max' =>
                'نام محله یا منطقه نباید بیشتر از ۱۰۰ کاراکتر باشد.',

            'address.max' =>
                'آدرس نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',

            'latitude.numeric' =>
                'Latitude باید عدد باشد.',

            'latitude.between' =>
                'Latitude معتبر نیست.',

            'longitude.numeric' =>
                'Longitude باید عدد باشد.',

            'longitude.between' =>
                'Longitude معتبر نیست.',

            'is_active.boolean' =>
                'وضعیت سالن معتبر نیست.',
        ];
    }
}
