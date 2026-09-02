<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;

class BarberRequest extends FormRequest
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

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'specialty' => [
                'nullable',
                'string',
                'max:150',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'remove_image' => [
                'nullable',
                'boolean',
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
                'نام آرایشگر الزامی است.',

            'name.string' =>
                'نام آرایشگر معتبر نیست.',

            'name.min' =>
                'نام آرایشگر حداقل باید ۲ کاراکتر باشد.',

            'name.max' =>
                'نام آرایشگر نباید بیشتر از ۱۵۰ کاراکتر باشد.',

            'phone.string' =>
                'شماره تماس آرایشگر معتبر نیست.',

            'phone.max' =>
                'شماره تماس آرایشگر نباید بیشتر از ۳۰ کاراکتر باشد.',

            'bio.string' =>
                'توضیحات آرایشگر معتبر نیست.',

            'bio.max' =>
                'توضیحات آرایشگر نباید بیشتر از ۵۰۰۰ کاراکتر باشد.',

            'specialty.string' =>
                'تخصص آرایشگر معتبر نیست.',

            'specialty.max' =>
                'تخصص آرایشگر نباید بیشتر از ۱۵۰ کاراکتر باشد.',

            'image.image' =>
                'فایل تصویر آرایشگر باید تصویر باشد.',

            'image.mimes' =>
                'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',

            'image.max' =>
                'حجم تصویر آرایشگر نباید بیشتر از ۴ مگابایت باشد.',

            'remove_image.boolean' =>
                'مقدار حذف تصویر معتبر نیست.',

            'is_active.boolean' =>
                'وضعیت آرایشگر معتبر نیست.',
        ];
    }
}
