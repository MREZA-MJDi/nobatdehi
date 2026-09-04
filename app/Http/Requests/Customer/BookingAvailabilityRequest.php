<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class BookingAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

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

        ];
    }


    public function messages(): array
    {
        return [
            'barber_id.required' =>
                'آرایشگر را انتخاب کنید.',

            'service_id.required' =>
                'خدمت را انتخاب کنید.',

            'booking_date.required' =>
                'تاریخ را انتخاب کنید.',

            'booking_date.date_format' =>
                'تاریخ معتبر نیست.',
        ];
    }
}
