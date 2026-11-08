<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has permission (admins always have all permissions)
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // Return error or redirect based on role
        return redirect()->back()->with('error', 'Vous n\'avez pas les droits nécessaires pour accéder à cette fonctionnalité.');
    }
}
