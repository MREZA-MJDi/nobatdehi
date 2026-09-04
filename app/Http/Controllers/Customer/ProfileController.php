<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(
        Request $request
    ): View {
        return view(
            'customer.account.profile'
        );
    }


    public function update(
        Request $request
    ): RedirectResponse {

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
                ],
            ],
            [
                'name.required' =>
                    'نام الزامی است.',

                'name.min' =>
                    'نام حداقل باید ۲ کاراکتر باشد.',

                'email.email' =>
                    'ایمیل معتبر نیست.',
            ]
        );


        $request->user()->update([
            'name' =>
                trim($data['name']),

            'email' =>
                $data['email'] ?? null,
        ]);


        return back()->with(
            'success',
            'اطلاعات حساب با موفقیت ذخیره شد.'
        );
    }
}
