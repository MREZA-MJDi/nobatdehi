<?php

namespace App\Http\Requests\Salon;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ManualBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('customer_phone', '');

        if ($phone !== '') {
            try {
                $phone = PhoneNumber::normalize($phone);
            } catch (\Throwable) {
                // Validation will return the user-friendly phone error.
            }
        }

        $this->merge([
            'customer_name' => trim((string) $this->input('customer_name', '')),
            'customer_phone' => $phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'customer_phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
            ],

            'manual_confirmed' => [
                'accepted',
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
            'customer_name.required' =>
                'نام مشتری را وارد کنید.',
            'customer_name.min' =>
                'نام مشتری خیلی کوتاه است.',
            'customer_name.max' =>
                'نام مشتری نباید بیشتر از ۱۲۰ کاراکتر باشد.',

            'customer_phone.required' =>
                'شماره موبایل مشتری را وارد کنید.',
            'customer_phone.regex' =>
                'شماره موبایل مشتری معتبر نیست.',

            'manual_confirmed.accepted' =>
                'لطفاً ثبت نوبت به‌صورت دستی را تأیید کنید.',

            'barber_id.required' =>
                'آرایشگر را انتخاب کنید.',
            'barber_id.exists' =>
                'آرایشگر انتخاب‌شده معتبر نیست.',

            'service_id.required' =>
                'خدمت را انتخاب کنید.',
            'service_id.exists' =>
                'خدمت انتخاب‌شده معتبر نیست.',

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
                'توضیحات نوبت نباید بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ];
    }
}
