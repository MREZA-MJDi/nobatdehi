<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateSalonPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'current_password:web',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'رمز عبور فعلی الزامی است.',
            'current_password.current_password' => 'رمز عبور فعلی صحیح نیست.',
            'password.required' => 'رمز عبور جدید الزامی است.',
            'password.confirmed' => 'تکرار رمز عبور جدید با آن یکسان نیست.',
            'password.min' => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
        ];
    }
}
