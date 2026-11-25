<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        $userRole = strtolower(trim($user->role ?? ''));
        $normalizedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);

        if (!in_array($userRole, $normalizedRoles)) {
            abort(403, 'Akses tidak diizinkan untuk role ' . ($user->role ?? 'User') . '.');
        }

        return $next($request);
    }
}
