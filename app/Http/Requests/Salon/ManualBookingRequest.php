<?php

namespace App\Http\Requests\Salon;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(
                        'role',
                        UserRole::CUSTOMER->value
                    ),
            ],

            'barber_id' => [
                'required',
                'integer',
                'exists:barbers,id',
            ],

            'service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],

            'booking_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' =>
                'مشتری را انتخاب کنید.',

            'customer_id.exists' =>
                'مشتری انتخاب شده معتبر نیست.',

            'barber_id.required' =>
                'آرایشگر را انتخاب کنید.',

            'barber_id.exists' =>
                'آرایشگر انتخاب شده معتبر نیست.',

            'service_id.required' =>
                'خدمت را انتخاب کنید.',

            'service_id.exists' =>
                'خدمت انتخاب شده معتبر نیست.',

            'booking_date.required' =>
                'تاریخ را انتخاب کنید.',

            'booking_date.date_format' =>
                'تاریخ نوبت معتبر نیست.',

            'start_time.required' =>
                'ساعت را انتخاب کنید.',

            'start_time.date_format' =>
                'ساعت نوبت معتبر نیست.',

            'notes.string' =>
                'توضیحات نوبت معتبر نیست.',

            'notes.max' =>
                'توضیحات نوبت نباید بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ];
    }
}
