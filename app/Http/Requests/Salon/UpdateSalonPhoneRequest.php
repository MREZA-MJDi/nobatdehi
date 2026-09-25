<?php

namespace App\Http\Requests\Salon;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalonPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
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
                Rule::unique('users', 'phone')
                    ->ignore($this->user()?->id),
            ],
            'current_password' => [
                'required',
                'current_password:web',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'شماره موبایل معتبر نیست.',
            'phone.unique' => 'این شماره موبایل قبلاً برای حساب دیگری ثبت شده است.',
            'current_password.required' => 'برای تغییر شماره، رمز عبور فعلی را وارد کنید.',
            'current_password.current_password' => 'رمز عبور فعلی صحیح نیست.',
        ];
    }
}
