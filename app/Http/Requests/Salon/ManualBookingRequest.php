<?php

namespace App\Http\Requests\Salon;

use App\Enums\UserRole;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('new_customer_phone', '');

        if ($phone !== '') {
            try {
                $phone = PhoneNumber::normalize($phone);
            } catch (\Throwable) {
                // Let validation return the user-friendly phone error.
            }
        }

        $this->merge([
            'customer_id' => $this->input('customer_id') ?: null,
            'new_customer_name' => trim((string) $this->input('new_customer_name', '')),
            'new_customer_phone' => $phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'integer',
                'required_without_all:new_customer_name,new_customer_phone',
                Rule::exists('users', 'id')->where(
                    'role',
                    UserRole::CUSTOMER->value
                ),
            ],

            'new_customer_name' => [
                'nullable',
                'string',
                'min:2',
                'max:120',
                'required_without:customer_id',
            ],

            'new_customer_phone' => [
                'nullable',
                'string',
                'regex:/^09\d{9}$/',
                'required_without:customer_id',
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
            'customer_id.required_without_all' =>
                'یک مشتری موجود را انتخاب کنید یا مشتری جدید بسازید.',

            'customer_id.exists' =>
                'مشتری انتخاب شده معتبر نیست.',

            'new_customer_name.required_without' =>
                'نام مشتری جدید را وارد کنید.',

            'new_customer_name.min' =>
                'نام مشتری جدید خیلی کوتاه است.',

            'new_customer_phone.required_without' =>
                'شماره موبایل مشتری جدید را وارد کنید.',

            'new_customer_phone.regex' =>
                'شماره موبایل مشتری جدید معتبر نیست.',

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
