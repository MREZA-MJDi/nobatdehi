<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        try {
            $phone = PhoneNumber::normalize(
                $this->input('phone', '')
            );

            $this->merge([
                'phone' => $phone,
            ]);
        } catch (\Throwable) {
            $this->merge([
                'phone' => $this->input('phone', ''),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'phone' => [
                'required',
                'regex:/^09\d{9}$/',
                Rule::unique(
                    'users',
                    'phone'
                ),
            ],

            'terms' => [
                'required',
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'نام و نام خانوادگی الزامی است.',

            'name.string' =>
                'نام و نام خانوادگی معتبر نیست.',

            'name.min' =>
                'نام و نام خانوادگی حداقل باید ۲ کاراکتر باشد.',

            'name.max' =>
                'نام و نام خانوادگی نباید بیشتر از ۱۲۰ کاراکتر باشد.',

            'phone.required' =>
                'شماره موبایل الزامی است.',

            'phone.regex' =>
                'شماره موبایل معتبر نیست.',

            'phone.unique' =>
                'این شماره موبایل قبلاً ثبت شده است. وارد شوید.',

            'terms.required' =>
                'پذیرش قوانین الزامی است.',

            'terms.accepted' =>
                'برای ثبت‌نام باید قوانین و شرایط استفاده را بپذیرید.',
        ];
    }
}
