<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:190',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' =>
                'ایمیل الزامی است.',

            'email.email' =>
                'ایمیل وارد شده معتبر نیست.',
        ];
    }
}
