<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

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
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is suspended
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'username' => 'Votre compte a été suspendu par l\'administrateur.',
            ]);
        }

        // Check roles
        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        // Redirect based on role if unauthorized
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'secretary':
                return redirect()->route('secretary.dashboard');
            case 'collector':
                return redirect()->route('collector.dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'username' => 'Accès non autorisé.',
                ]);
        }
    }
}
