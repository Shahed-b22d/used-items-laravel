<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyActor
{
    public function handle(Request $request, Closure $next)
    {
        foreach (['user', 'store', 'admin'] as $guard) {
            if (auth($guard)->check()) {
                $request->merge(['actor' => auth($guard)->user()]);
                $request->merge(['actor_guard' => $guard]);
                return $next($request);
            }
        }

        return response()->json(['error' => 'غير مصرح'], 401);
    }
}
