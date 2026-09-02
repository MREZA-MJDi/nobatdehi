<?php

namespace App\Http\Requests\Salon;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(
                    BookingStatus::class
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' =>
                'وضعیت نوبت الزامی است.',

            'status.enum' =>
                'وضعیت انتخاب شده معتبر نیست.',
        ];
    }
}
