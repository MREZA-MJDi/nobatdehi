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
            //
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
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
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'نام الزامی است.',

            'name.min' =>
                'نام باید حداقل ۲ کاراکتر باشد.',

            'phone.required' =>
                'شماره موبایل الزامی است.',

            'phone.regex' =>
                'شماره موبایل معتبر نیست.',

            'phone.unique' =>
                'این شماره قبلاً ثبت شده است. وارد حساب خود شوید.',

            'terms.accepted' =>
                'پذیرش قوانین الزامی است.',
        ];
    }
}
