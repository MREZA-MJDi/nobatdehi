<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:600',
            ],

            'price' => [
                'required',
                'integer',
                'min:0',
                'max:999999999999',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'نام خدمت الزامی است.',

            'name.string' =>
                'نام خدمت معتبر نیست.',

            'name.min' =>
                'نام خدمت حداقل باید ۲ کاراکتر باشد.',

            'name.max' =>
                'نام خدمت نباید بیشتر از ۱۵۰ کاراکتر باشد.',

            'description.string' =>
                'توضیحات خدمت معتبر نیست.',

            'description.max' =>
                'توضیحات خدمت نباید بیشتر از ۵۰۰۰ کاراکتر باشد.',

            'duration_minutes.required' =>
                'مدت زمان خدمت الزامی است.',

            'duration_minutes.integer' =>
                'مدت زمان خدمت باید عدد باشد.',

            'duration_minutes.min' =>
                'مدت زمان خدمت حداقل باید ۵ دقیقه باشد.',

            'duration_minutes.max' =>
                'مدت زمان خدمت نمی‌تواند بیشتر از ۶۰۰ دقیقه باشد.',

            'price.required' =>
                'قیمت خدمت الزامی است.',

            'price.integer' =>
                'قیمت خدمت باید عدد باشد.',

            'price.min' =>
                'قیمت خدمت نمی‌تواند منفی باشد.',

            'price.max' =>
                'قیمت خدمت بیش از حد مجاز است.',

            'sort_order.integer' =>
                'ترتیب نمایش باید عدد باشد.',

            'sort_order.min' =>
                'ترتیب نمایش نمی‌تواند منفی باشد.',

            'is_active.boolean' =>
                'وضعیت خدمت معتبر نیست.',
        ];
    }
}
