<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        return [
            'title' => [
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

            'barber_id' => [
                'nullable',
                'integer',
                'exists:barbers,id',
            ],

            'service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],

            'before_image' => [
                'required_without:existing_before_image',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'after_image' => [
                'required_without:existing_after_image',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'existing_before_image' => [
                'nullable',
                'string',
            ],

            'existing_after_image' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' =>
                'عنوان نمونه‌کار الزامی است.',

            'title.min' =>
                'عنوان نمونه‌کار خیلی کوتاه است.',

            'barber_id.exists' =>
                'آرایشگر انتخاب شده معتبر نیست.',

            'service_id.exists' =>
                'خدمت انتخاب شده معتبر نیست.',

            'before_image.required_without' =>
                'عکس قبل را انتخاب کنید.',

            'before_image.image' =>
                'عکس قبل باید یک تصویر باشد.',

            'before_image.mimes' =>
                'فرمت عکس قبل باید jpg، jpeg، png یا webp باشد.',

            'before_image.max' =>
                'حجم عکس قبل نباید بیشتر از ۵ مگابایت باشد.',

            'after_image.required_without' =>
                'عکس بعد را انتخاب کنید.',

            'after_image.image' =>
                'عکس بعد باید یک تصویر باشد.',

            'after_image.mimes' =>
                'فرمت عکس بعد باید jpg، jpeg، png یا webp باشد.',

            'after_image.max' =>
                'حجم عکس بعد نباید بیشتر از ۵ مگابایت باشد.',
        ];
    }
}
