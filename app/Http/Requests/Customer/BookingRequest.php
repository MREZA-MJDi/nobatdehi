<?php

namespace App\Http\Requests\Customer;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::CUSTOMER) === true;
    }

    public function rules(): array
    {
        return [
            'salon_id' => [
                'required',
                'integer',
                'exists:salons,id',
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
            'salon_id.required' =>
                'سالن انتخاب نشده است.',

            'salon_id.exists' =>
                'سالن انتخاب‌شده معتبر نیست.',

            'barber_id.required' =>
                'آرایشگر را انتخاب کنید.',

            'barber_id.exists' =>
                'آرایشگر انتخاب‌شده معتبر نیست.',

            'service_id.required' =>
                'خدمت را انتخاب کنید.',

            'service_id.exists' =>
                'خدمت انتخاب‌شده معتبر نیست.',

            'booking_date.required' =>
                'تاریخ را انتخاب کنید.',

            'booking_date.date_format' =>
                'تاریخ معتبر نیست.',

            'start_time.required' =>
                'ساعت را انتخاب کنید.',

            'start_time.date_format' =>
                'ساعت معتبر نیست.',

            'notes.string' =>
                'توضیحات نوبت معتبر نیست.',

            'notes.max' =>
                'توضیحات نوبت نباید بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ];
    }
}
