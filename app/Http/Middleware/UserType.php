<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class UserType {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $type) {
        $user = Auth::user();
        if ($type === 'Admin' ? $user->isAdmin() : $user->type === $type) {
            return $next($request);
        } else {
            return redirect()->route('dashboard');
        }
    }
}
