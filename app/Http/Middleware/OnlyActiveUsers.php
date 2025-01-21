<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Auth\AuthenticationException;

class OnlyActiveUsers {

    public function handle($request, Closure $next) {
        if (auth()->user()->active === null) {
            Auth::logout();
            throw new AuthenticationException(
                message: 'Unauthenticated.',
                redirectTo: route('notice', ['suspended'])
            );
        }

        return $next($request);
    }
}
