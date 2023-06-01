<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $this->authenticate($request, $guards);

        if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()->is_banned) {
            Auth::guard('sanctum')->user()->tokens()->delete();

            return response([
                'status' => false,
                'error' => 'You are banned',
                'errors' => (object)[],
            ], 403);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
