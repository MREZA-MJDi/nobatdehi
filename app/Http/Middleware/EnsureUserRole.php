<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(
        Request $request,
        Closure $next,
                ...$roles
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | User must be authenticated
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (!$user) {
            abort(401);
        }


        /*
        |--------------------------------------------------------------------------
        | User must have one of the required roles
        |--------------------------------------------------------------------------
        */

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Forbidden
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'شما اجازه دسترسی به این بخش را ندارید.'
        );
    }
}
