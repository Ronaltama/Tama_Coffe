<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Check if user has superadmin or admin role
        if (!$user->role || !in_array($user->role->name, ['superadmin', 'admin'])) {
            Auth::logout();
            return redirect('/login')->withErrors(['email' => 'You do not have permission to access this area.']);
        }

        return $next($request);
    }
}
