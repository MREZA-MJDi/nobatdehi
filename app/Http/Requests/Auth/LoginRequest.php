<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Input
    |--------------------------------------------------------------------------
    */

    protected function prepareForValidation(): void
    {
        $rawPhone = (string) $this->input(
            'phone',
            ''
        );

        try {
            $phone = PhoneNumber::normalize(
                $rawPhone
            );
        } catch (\Throwable) {
            $phone = $rawPhone;
        }

        $this->merge([
            'phone' => $phone,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
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


    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

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
