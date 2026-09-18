<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view(
            'customer.account.profile',
            [
                'user' => $request->user(),
            ]
        );
    }


    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:120',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:190',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
            ],
            [
                'name.required' =>
                    'نام الزامی است.',

                'name.string' =>
                    'نام واردشده معتبر نیست.',

                'name.min' =>
                    'نام حداقل باید ۲ کاراکتر باشد.',

                'name.max' =>
                    'نام نمی‌تواند بیشتر از ۱۲۰ کاراکتر باشد.',

                'email.email' =>
                    'ایمیل معتبر نیست.',

                'email.max' =>
                    'ایمیل نمی‌تواند بیشتر از ۱۹۰ کاراکتر باشد.',

                'email.unique' =>
                    'این ایمیل قبلاً استفاده شده است.',
            ]
        );

        $user->update([
            'name' => trim($data['name']),
            'email' => filled($data['email'])
                ? trim($data['email'])
                : null,
        ]);

        return back()->with(
            'success',
            'اطلاعات پروفایل با موفقیت ذخیره شد.'
        );
    }
}
