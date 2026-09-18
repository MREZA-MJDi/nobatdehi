<?php

namespace App\Http\Requests\Salon;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSalonOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Auto detect basic type when JS is unavailable
        |--------------------------------------------------------------------------
        */

        if (
            !$this->input('type')
            && $this->hasFile('media')
        ) {
            $file = $this->file('media');

            $mime = strtolower(
                (string) $file->getMimeType()
            );

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $type = null;

            if (
                $mime === 'image/gif'
                || $extension === 'gif'
            ) {
                $type = PostType::GIF->value;
            } elseif (
                str_starts_with(
                    $mime,
                    'image/'
                )
            ) {
                $type = PostType::PHOTO->value;
            } elseif (
                str_starts_with(
                    $mime,
                    'video/'
                )
            ) {
                $type = PostType::VIDEO->value;
            }

            if ($type) {
                $this->merge([
                    'type' => $type,
                ]);
            }
        }
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
                'nullable',
                Rule::enum(PostType::class),
            ],

            /*
            |--------------------------------------------------------------------------
            | Media
            |--------------------------------------------------------------------------
            */

            'media' => [
                $isCreate
                    ? 'required'
                    : 'nullable',

                'file',

                'max:51200',

                'mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov',
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

    public function withValidator(
        $validator
    ): void {
        $validator->after(
            function ($validator) {

                $media = $this->file(
                    'media'
                );

                $selectedType = $this->input(
                    'type'
                );

                /*
                |--------------------------------------------------------------------------
                | No new file on update
                |--------------------------------------------------------------------------
                */

                if (!$media) {

                    $post = $this->route(
                        'post'
                    );

                    if (
                        $post instanceof Post
                        && $selectedType
                    ) {
                        $currentType =
                            $post->type instanceof PostType
                                ? $post->type->value
                                : (string) $post->type;

                        if (
                            $currentType !== $selectedType
                        ) {
                            $validator->errors()->add(
                                'media',
                                'برای تغییر نوع رسانه باید فایل جدیدی انتخاب کنید.'
                            );
                        }
                    }

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Actual file detection
                |--------------------------------------------------------------------------
                */

                $mime = strtolower(
                    (string) $media->getMimeType()
                );

                $extension = strtolower(
                    $media->getClientOriginalExtension()
                );

                $isGif =
                    $mime === 'image/gif'
                    || $extension === 'gif';

                $isImage =
                    str_starts_with(
                        $mime,
                        'image/'
                    )
                    && !$isGif;

                $isVideo =
                    str_starts_with(
                        $mime,
                        'video/'
                    );

                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */

                if (
                    $selectedType === PostType::PHOTO->value
                    && !$isImage
                ) {
                    $validator->errors()->add(
                        'media',
                        'برای نوع عکس باید یک فایل تصویری معمولی انتخاب کنید.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | GIF
                |--------------------------------------------------------------------------
                */

                if (
                    $selectedType === PostType::GIF->value
                    && !$isGif
                ) {
                    $validator->errors()->add(
                        'media',
                        'برای GIF باید فایل GIF انتخاب کنید.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Video / Reel
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $selectedType,
                        [
                            PostType::VIDEO->value,
                            PostType::REEL->value,
                        ],
                        true
                    )
                    && !$isVideo
                ) {
                    $validator->errors()->add(
                        'media',
                        'برای ویدیو یا ریلز باید فایل ویدیویی انتخاب کنید.'
                    );
                }
            }
        );
    }

    public function messages(): array
    {
        return [
            'performed_by_owner.required' =>
                'انجام‌دهنده پست را مشخص کنید.',

            'performed_by_owner.boolean' =>
                'مقدار انجام‌دهنده نامعتبر است.',

            'barber_id.required_if' =>
                'آرایشگر انجام‌دهنده را انتخاب کنید.',

            'barber_id.exists' =>
                'آرایشگر انتخاب‌شده معتبر نیست.',

            'service_id.exists' =>
                'خدمت انتخاب‌شده معتبر نیست.',

            'type.enum' =>
                'نوع رسانه معتبر نیست.',

            'media.required' =>
                'فایل رسانه را انتخاب کنید.',

            'media.file' =>
                'فایل رسانه معتبر نیست.',

            'media.max' =>
                'حجم فایل رسانه نباید بیشتر از ۵۰ مگابایت باشد.',

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

            'is_active.boolean' =>
                'وضعیت انتشار نامعتبر است.',

            'sort_order.integer' =>
                'ترتیب نمایش باید عددی باشد.',

            'sort_order.min' =>
                'ترتیب نمایش نمی‌تواند منفی باشد.',

            'sort_order.max' =>
                'ترتیب نمایش بیش از حد مجاز است.',
        ];
    }
}
