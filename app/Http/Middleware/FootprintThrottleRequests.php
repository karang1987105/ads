<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

class FootprintThrottleRequests extends ThrottleRequests {
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '') {
        if (!$request->has('h')) {
            abort(404);
        }
        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }

    protected function resolveRequestSignature($request): string {
        return sha1($request->route()->getDomain() . '|' . $request->ip() . '|' . $request->input("f"));
    }

}
