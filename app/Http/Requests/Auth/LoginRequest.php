<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'phone' => [
                'required',
                'regex:/^09\d{9}$/',
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
        ];
    }
}
