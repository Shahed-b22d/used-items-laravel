<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('store')->check()) {
            return response()->json(['message' => 'Unauthorized - Must be a store'], 401);
        }
        return $next($request);
    }
}
