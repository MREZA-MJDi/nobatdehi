<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('salon.password.edit');
    }

    public function update(
        UpdatePasswordRequest $request
    ): RedirectResponse {
        $request->user()->update([
            'password' => $request->validated('password'),
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('salon.dashboard')
            ->with(
                'success',
                'رمز عبور با موفقیت تغییر کرد.'
            );
    }
}
