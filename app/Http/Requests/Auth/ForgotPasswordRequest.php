<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rawPhone = (string) $this->input('phone', '');

        try {
            $phone = PhoneNumber::normalize($rawPhone);
        } catch (\Throwable) {
            $phone = $rawPhone;
        }

        $this->merge([
            'phone' => $phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'شماره موبایل معتبر نیست.',
        ];
    }
}
