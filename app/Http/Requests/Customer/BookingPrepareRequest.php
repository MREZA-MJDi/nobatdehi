<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class BookingPrepareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

            'barber_id.required' =>
                'آرایشگر را انتخاب کنید.',

            'service_id.required' =>
                'خدمت را انتخاب کنید.',

            'booking_date.required' =>
                'تاریخ را انتخاب کنید.',

            'booking_date.date_format' =>
                'تاریخ معتبر نیست.',

            'start_time.required' =>
                'ساعت را انتخاب کنید.',

            'start_time.date_format' =>
                'ساعت معتبر نیست.',
        ];
    }
}
