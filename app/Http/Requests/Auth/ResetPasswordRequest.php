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
            'code' => [
                'required',
                'digits:6',
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
            'code.required' => 'کد تأیید الزامی است.',
            'code.digits' => 'کد تأیید باید ۶ رقمی باشد.',

            'password.required' => 'رمز عبور الزامی است.',
            'password.confirmed' => 'تکرار رمز عبور یکسان نیست.',
        ];
    }
}
