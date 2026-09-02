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

            'salon_id.exists' =>
                'سالن انتخاب شده پیدا نشد.',

            'barber_id.required' =>
                'آرایشگر را انتخاب کنید.',

            'barber_id.exists' =>
                'آرایشگر انتخاب شده پیدا نشد.',

            'service_id.required' =>
                'خدمت را انتخاب کنید.',

            'service_id.exists' =>
                'خدمت انتخاب شده پیدا نشد.',

            'booking_date.required' =>
                'تاریخ نوبت را انتخاب کنید.',

            'booking_date.date_format' =>
                'تاریخ نوبت معتبر نیست.',

            'start_time.required' =>
                'ساعت نوبت را انتخاب کنید.',

            'start_time.date_format' =>
                'ساعت نوبت معتبر نیست.',

            'notes.string' =>
                'توضیحات نوبت معتبر نیست.',

            'notes.max' =>
                'توضیحات نوبت بیش از حد مجاز است.',
        ];
    }
}
