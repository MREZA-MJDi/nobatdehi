<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(
                UserRole::SUPER_ADMIN
            ) === true;
    }

    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Owner / Account
            |--------------------------------------------------------------------------
            */

            'owner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:190',
            ],

            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],

            'primary_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'secondary_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Map / Location
            |--------------------------------------------------------------------------
            */

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

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
                'نام سالن الزامی است.',

            'name.min' =>
                'نام سالن حداقل باید ۲ کاراکتر باشد.',

            'owner_id.required' =>
                'حساب کنترل‌کننده سالن الزامی است.',

            'owner_id.exists' =>
                'حساب انتخاب شده وجود ندارد.',

            'email.email' =>
                'ایمیل وارد شده معتبر نیست.',

            'logo.image' =>
                'فایل لوگو باید تصویر باشد.',

            'logo.mimes' =>
                'فرمت لوگو باید jpg، jpeg، png یا webp باشد.',

            'logo.max' =>
                'حجم لوگو نباید بیشتر از ۴ مگابایت باشد.',

            'cover.image' =>
                'فایل تصویر اصلی باید تصویر باشد.',

            'cover.mimes' =>
                'فرمت تصویر اصلی باید jpg، jpeg، png یا webp باشد.',

            'cover.max' =>
                'حجم تصویر اصلی نباید بیشتر از ۸ مگابایت باشد.',

            'primary_color.regex' =>
                'رنگ اصلی معتبر نیست.',

            'secondary_color.regex' =>
                'رنگ مکمل معتبر نیست.',

            'latitude.numeric' =>
                'عرض جغرافیایی باید عدد باشد.',

            'latitude.between' =>
                'مختصات عرض جغرافیایی معتبر نیست.',

            'longitude.numeric' =>
                'طول جغرافیایی باید عدد باشد.',

            'longitude.between' =>
                'مختصات طول جغرافیایی معتبر نیست.',
        ];
    }
}
