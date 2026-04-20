<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function showRegistrationForm()
    {

        if (User::count() > 0 && !auth()->check()) {
            return redirect('/login')->with('error', 'Debes iniciar sesión como administrador');
        }

        if (User::count() > 0 && auth()->check() && !auth()->user()->isAdmin()) {
            abort(403, 'Solo administradores pueden registrar usuarios');
        }

        $clientes = Cliente::all();
        return view('auth.register', compact('clientes'));
    }

    public function register(Request $request)
    {
        // Validación con regla condicional para cliente_id
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,admin_n2,documentador,cliente',
            'cliente_id' => 'required_if:role,cliente|nullable|exists:clientes,id'
        ]);

        // Crear usuario con cliente_id si corresponde
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role']
        ];

        // Solo agregar cliente_id si el rol es 'cliente'
        if ($validated['role'] === 'cliente') {
            $userData['cliente_id'] = $validated['cliente_id'];
        }

        $user = User::create($userData);

        // Auto-login si es el primer usuario
        /*if (User::count() === 1) {
            Auth::login($user);
            return redirect('/admin/dashboard');
        }*/

        //return redirect('/login')->with('success', 'Usuario registrado. Por favor inicie sesión.');
        return redirect('/home')->with('success', 'Usuario registrado. Por favor inicie sesión.');

    }
}