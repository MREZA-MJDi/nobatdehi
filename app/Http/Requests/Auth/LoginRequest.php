<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        try {
            $this->merge([
                'phone' => PhoneNumber::normalize(
                    $this->input('phone', '')
                ),
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
            'phone' => [
                'required',
                'regex:/^09\d{9}$/',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' =>
                'شماره موبایل الزامی است.',

            'phone.regex' =>
                'شماره موبایل معتبر نیست.',

            'password.required' =>
                'رمز عبور الزامی است.',

            'password.min' =>
                'رمز عبور باید حداقل ۸ کاراکتر باشد.',

            'remember.boolean' =>
                'مقدار گزینه مرا به خاطر بسپار معتبر نیست.',
        ];
    }
}
