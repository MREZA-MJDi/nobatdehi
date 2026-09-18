<?php

namespace App\Services;

use App\Enums\PostType;
use App\Models\Salon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PostMediaService
{
    public function detectType(
        UploadedFile $file,
        ?string $requestedType = null
    ): PostType {
        $mime = strtolower(
            (string) $file->getMimeType()
        );

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        /*
        |--------------------------------------------------------------------------
        | GIF
        |--------------------------------------------------------------------------
        */

        if (
            $mime === 'image/gif'
            || $extension === 'gif'
        ) {
            return PostType::GIF;
        }

        /*
        |--------------------------------------------------------------------------
        | Normal image
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $mime,
                'image/'
            )
        ) {
            return PostType::PHOTO;
        }

        /*
        |--------------------------------------------------------------------------
        | Video / Reel
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $mime,
                'video/'
            )
        ) {
            return $requestedType === PostType::REEL->value
                ? PostType::REEL
                : PostType::VIDEO;
        }

        throw ValidationException::withMessages([
            'media' =>
                'نوع فایل رسانه توسط سیستم پشتیبانی نمی‌شود.',
        ]);
    }

    public function store(
        UploadedFile $file,
        Salon $salon
    ): string {
        return $file->store(
            'posts/' . $salon->id,
            'public'
        );
    }

    public function storeThumbnail(
        UploadedFile $file,
        Salon $salon
    ): string {
        return $file->store(
            'posts/' . $salon->id . '/thumbnails',
            'public'
        );
    }

    public function delete(
        ?string $path
    ): void {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete(
            $path
        );
    }

    public function url(
        ?string $path
    ): ?string {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url(
            $path
        );
    }
}
