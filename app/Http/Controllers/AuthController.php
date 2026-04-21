<?php
// Controlador que gestiona la autenticación, inicio y cierre de sesión de los usuarios.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect('/dashboard');
        }

        return back()->with('error', 'Correo o contraseña incorrectos');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
