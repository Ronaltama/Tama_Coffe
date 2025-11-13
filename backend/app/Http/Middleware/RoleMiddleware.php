<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!in_array($request->user()->role_id, $roles)) {
            return response()->json(['message' => 'Forbidden - role tidak diizinkan'], 403);
        }
        return $next($request);
    }
}

