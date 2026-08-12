<?php
// Controlador que gestiona la autenticación, inicio y cierre de sesión de los usuarios.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login.' . Str::lower($request->email) . '.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $segundos = RateLimiter::availableIn($key);
            return back()->with('error', "Demasiados intentos fallidos. Intenta de nuevo en {$segundos} segundos.");
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            RateLimiter::clear($key);
            
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();
            
            // Contar sesiones activas de otros dispositivos en los últimos 15 minutos
            $otherActiveSessionsCount = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                ->count();

            if ($otherActiveSessionsCount >= 2) {
                Auth::logout();
                return back()->with('error', 'Límite de sesiones alcanzado.');
            }

            $request->session()->regenerate();
            return redirect('/dashboard');
        }

        RateLimiter::hit($key, 60);

        return back()->with('error', 'Correo o contraseña incorrectos.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Login rápido con la cuenta demo (para demostración de portafolio).
     */
    public function loginDemo(Request $request)
    {
        $demo = User::where('email', 'demo@impulweb.test')->first();

        if (!$demo) {
            return redirect('/login')->with('error', 'La cuenta demo no está configurada. Ejecuta el seeder primero.');
        }

        Auth::login($demo);
        $request->session()->regenerate();

        return redirect('/dashboard');
    }
}
