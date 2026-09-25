<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireCustomerInactivity
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $timeoutMinutes = max(
            1,
            (int) env('CUSTOMER_INACTIVITY_MINUTES', 30)
        );

        $lastActivity = $request->session()->get('customer.last_activity_at');

        if (
            $lastActivity !== null
            && now()->timestamp - (int) $lastActivity >= $timeoutMinutes * 60
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login', ['entry' => 'customer'])
                ->with(
                    'status',
                    'به‌دلیل عدم فعالیت، از حساب خارج شدی. دوباره وارد شو.'
                );
        }

        $request->session()->put(
            'customer.last_activity_at',
            now()->timestamp
        );

        return $next($request);
    }
}
