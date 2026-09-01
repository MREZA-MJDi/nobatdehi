<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email',
                'max:190',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' =>
                'ایمیل الزامی است.',

            'email.email' =>
                'ایمیل معتبر نیست.',

            'password.required' =>
                'رمز عبور الزامی است.',

            'password.confirmed' =>
                'تکرار رمز عبور یکسان نیست.',
        ];
    }
}
