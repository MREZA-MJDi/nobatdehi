<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' =>
                'رمز عبور جدید الزامی است.',

            'password.min' =>
                'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',

            'password.confirmed' =>
                'تکرار رمز عبور یکسان نیست.',
        ];
    }
}
