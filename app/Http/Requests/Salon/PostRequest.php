<?php

namespace App\Http\Requests\Salon;

use App\Enums\PostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');

        return [
            /*
            |--------------------------------------------------------------------------
            | Performer
            |--------------------------------------------------------------------------
            */

            'performed_by_owner' => [
                'required',
                'boolean',
            ],

            'barber_id' => [
                'nullable',
                'integer',
                'exists:barbers,id',
                'required_if:performed_by_owner,false',
            ],

            /*
            |--------------------------------------------------------------------------
            | Service
            |--------------------------------------------------------------------------
            */

            'service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */

            'type' => [
                'required',
                Rule::enum(PostType::class),
            ],

            /*
            |--------------------------------------------------------------------------
            | Media
            |--------------------------------------------------------------------------
            */

            'media' => [
                $isCreate ? 'required' : 'nullable',

                'file',

                'max:10240',

                'mimes:
                    jpg,
                    jpeg,
                    png,
                    webp,
                    gif,
                    mp4,
                    webm,
                    mov',
            ],

            /*
            |--------------------------------------------------------------------------
            | Thumbnail
            |--------------------------------------------------------------------------
            */

            'thumbnail' => [
                'nullable',

                'file',

                'max:5120',

                'mimes:jpg,jpeg,png,webp',
            ],

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            'title' => [
                'nullable',
                'string',
                'max:150',
            ],

            'caption' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Visibility
            |--------------------------------------------------------------------------
            */

            'is_active' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ordering
            |--------------------------------------------------------------------------
            */

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:4294967295',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'performed_by_owner.required' =>
                'انجام‌دهنده پست را مشخص کنید.',

            'barber_id.required_if' =>
                'آرایشگر انجام‌دهنده را انتخاب کنید.',

            'barber_id.exists' =>
                'آرایشگر انتخاب‌شده معتبر نیست.',

            'service_id.exists' =>
                'خدمت انتخاب‌شده معتبر نیست.',

            'type.required' =>
                'نوع پست را انتخاب کنید.',

            'type.enum' =>
                'نوع پست معتبر نیست.',

            'media.required' =>
                'فایل رسانه را انتخاب کنید.',

            'media.file' =>
                'فایل رسانه معتبر نیست.',

            'media.max' =>
                'حجم فایل رسانه نباید بیشتر از ۱۰ مگابایت باشد.',

            'media.mimes' =>
                'فرمت فایل رسانه مجاز نیست.',

            'thumbnail.file' =>
                'فایل کاور معتبر نیست.',

            'thumbnail.max' =>
                'حجم کاور نباید بیشتر از ۵ مگابایت باشد.',

            'thumbnail.mimes' =>
                'فرمت کاور مجاز نیست.',

            'title.max' =>
                'عنوان نمی‌تواند بیشتر از ۱۵۰ کاراکتر باشد.',

            'caption.max' =>
                'کپشن نمی‌تواند بیشتر از ۵۰۰۰ کاراکتر باشد.',
        ];
    }
}
