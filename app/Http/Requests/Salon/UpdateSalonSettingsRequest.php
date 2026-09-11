<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalonSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
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
                'max:255',
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
                'max:5120',
            ],

            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_logo' => [
                'nullable',
                'boolean',
            ],

            'remove_cover' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Colors
            |--------------------------------------------------------------------------
            */

            'primary_color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{3,8}$/',
            ],

            'secondary_color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{3,8}$/',
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
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Map
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
            | Working Hours
            |--------------------------------------------------------------------------
            */

            'working_hours' => [
                'nullable',
                'array',
            ],

            'working_hours.*' => [
                'array',
            ],

            'working_hours.*.*.start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'working_hours.*.*.end_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'working_hours.*.*.is_closed' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $workingHours =
                $this->input(
                    'working_hours',
                    []
                );

            foreach (
                $workingHours as $day => $intervals
            ) {

                foreach (
                    $intervals as $sortOrder => $interval
                ) {

                    $isClosed =
                        !empty(
                        $interval['is_closed']
                        );

                    if ($isClosed) {
                        continue;
                    }

                    $start =
                        $interval['start_time']
                        ?? null;

                    $end =
                        $interval['end_time']
                        ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | Both or None
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ($start && !$end) ||
                        (!$start && $end)
                    ) {

                        $validator->errors()->add(
                            "working_hours.$day.$sortOrder.start_time",
                            'برای بازه کاری، ساعت شروع و پایان را کامل وارد کنید.'
                        );

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | End Must Be After Start
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $start &&
                        $end &&
                        $start >= $end
                    ) {

                        $validator->errors()->add(
                            "working_hours.$day.$sortOrder.end_time",
                            'ساعت پایان باید بعد از ساعت شروع باشد.'
                        );
                    }
                }
            }
        });
    }
}
