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
                Rule::enum(BookingStatus::class),
            ],

            'override_priority' => [
                'nullable',
                'boolean',
            ],

            'priority_override_reason' => [
                'nullable',
                'string',
                'max:1000',
                'required_if:override_priority,1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'وضعیت نوبت الزامی است.',

            'status.enum' => 'وضعیت انتخاب شده معتبر نیست.',

            'override_priority.boolean' => 'مقدار تغییر اولویت معتبر نیست.',

            'priority_override_reason.string' => 'دلیل تغییر اولویت معتبر نیست.',

            'priority_override_reason.max' => 'دلیل تغییر اولویت نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',

            'priority_override_reason.required_if' => 'برای تغییر اولویت، وارد کردن دلیل الزامی است.',
        ];
    }
}
