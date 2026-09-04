<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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

            $phone =
                $rawPhone;
        }


        $this->merge([
            'phone' =>
                $phone,
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


            'terms' => [
                'required',
                'accepted',
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

            'name.required' =>
                'نام و نام خانوادگی الزامی است.',

            'name.string' =>
                'نام و نام خانوادگی معتبر نیست.',

            'name.min' =>
                'نام و نام خانوادگی حداقل باید ۲ کاراکتر باشد.',

            'name.max' =>
                'نام و نام خانوادگی نباید بیشتر از ۱۲۰ کاراکتر باشد.',


            'phone.required' =>
                'شماره موبایل الزامی است.',

            'phone.string' =>
                'شماره موبایل معتبر نیست.',

            'phone.regex' =>
                'شماره موبایل معتبر نیست.',

            'phone.unique' =>
                'این شماره موبایل قبلاً ثبت شده است. وارد شوید.',


            'terms.required' =>
                'پذیرش قوانین الزامی است.',

            'terms.accepted' =>
                'برای ثبت‌نام باید قوانین و شرایط استفاده را بپذیرید.',

        ];
    }
}
