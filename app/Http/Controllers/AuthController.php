<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user is suspended
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Votre compte a été suspendu par l\'administrateur.',
                ]);
            }

            // Update last login
            $user->update([
                'last_login_at' => Carbon::now(),
            ]);

            // Create Audit Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'description' => "Connexion réussie de l'utilisateur {$user->firstname} {$user->name} (Rôle: {$user->role}).",
                'ip_address' => $request->ip(),
            ]);

            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'username' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'description' => "Déconnexion de l'utilisateur {$user->firstname} {$user->name}.",
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Helper to redirect user based on their role.
     */
    private function redirectBasedOnRole($user)
    {
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
                    'username' => 'Rôle utilisateur inconnu.',
                ]);
        }
    }
}
