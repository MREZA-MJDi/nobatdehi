<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (
            $user
            && $user->isSalonOwner()
            && $user->must_change_password
        ) {
            return redirect()
                ->route('salon.password.edit')
                ->with(
                    'status',
                    'برای امنیت حساب، قبل از ادامه رمز اولیه را تغییر دهید.'
                );
        }

        return $next($request);
    }
}
