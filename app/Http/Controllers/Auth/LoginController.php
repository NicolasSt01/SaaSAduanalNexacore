<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            // Redirección según rol
            switch (auth()->user()->role) {
                case 'admin':
                    return redirect()->route('dashboardadmin');
                    //return redirect('/dashboard');
                case 'Documentador':
                    return redirect()->route('operaciones');
                    //return redirect('/operaciones');
                case 'ClienteAdmin':
                    return redirect()->route('clientes.dashboard');
                    //return redirect('/expedientes');
                case 'cliente':
                    return redirect()->route('clientes.dashboard');
                    //return redirect('/clientes/index');
                default:
                    return redirect('/');
            }
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}