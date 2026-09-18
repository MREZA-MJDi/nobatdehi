<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() === true;
    }

    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' =>
                'امتیاز را انتخاب کنید.',

            'rating.integer' =>
                'امتیاز معتبر نیست.',

            'rating.min' =>
                'امتیاز حداقل ۱ است.',

            'rating.max' =>
                'امتیاز حداکثر ۵ است.',

            'comment.max' =>
                'متن نظر نباید بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ];
    }
}
