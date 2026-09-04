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
        $rawPhone =
            (string) $this->input(
                'phone',
                ''
            );


        try {
            $phone =
                PhoneNumber::normalize(
                    $rawPhone
                );
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

            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
                Rule::unique(
                    'users',
                    'phone'
                ),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
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

            'phone.required' =>
                'شماره موبایل الزامی است.',

            'phone.regex' =>
                'شماره موبایل معتبر نیست.',

            'phone.unique' =>
                'این شماره قبلاً ثبت شده است.',

            'password.required' =>
                'رمز عبور الزامی است.',

            'password.min' =>
                'رمز عبور باید حداقل ۸ کاراکتر باشد.',

            'password.confirmed' =>
                'تکرار رمز عبور یکسان نیست.',

            'terms.accepted' =>
                'پذیرش قوانین الزامی است.',
        ];
    }
}
