<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /*if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            //return redirect()->intended('/'); // Redirige a dashboard o inicio
            $user = auth()->user();

            //Verificamos si el usuario esta activo o no
            if (!$user->active) {
                Auth::logout(); //Cierra la sesion inmediatamente
                return redirect()->route('inactive.user');
            }



            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.admindashboard');
                //return redirect('/dashboard');
                case 'Documentador':
                    return redirect()->route('documentador.dashboard');
                //return redirect('/operaciones');
                case 'ClienteAdmin':
                    return redirect()->route('cliente.admindashboard');
                //return redirect('/expedientes');
                case 'cliente':
                    return redirect()->route('clientes.dashboard');
                //return redirect('/clientes/index');
                case 'Trafico':
                    return redirect()->route('trafico.index');
                default:
                    return redirect('/');
            }
        }*/
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = auth()->user();

            if (!$user->active) {
                Auth::logout();
                return redirect()->route('inactive.user');
            }

            // Registrar la sesión activa para forzar sesión única por usuario
            $user->update(['active_session_id' => session()->getId()]);

            $role = strtolower($user->role);
            $route = config("dashboards.role_routes.{$role}", 'home');
            return redirect()->route($route);
        }

        return back()->with('error', 'Las credenciales no son válidas.')->withInput();






        // return back()->with('error', 'Las credenciales no son válidas.')->withInput();
    }

    public function logout(Request $request)
    {
        // Limpiar la sesión activa registrada
        if (Auth::check()) {
            Auth::user()->update(['active_session_id' => null]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
