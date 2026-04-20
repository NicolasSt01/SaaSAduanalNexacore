<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Redirigir según el tipo de usuario después de restablecer contraseña.
     */
    protected function redirectTo()
    {
        if (!auth()->check()) {
            return '/login';
        }

        $user = auth()->user();

        // Super Admin
        if ($user->isSuperAdmin()) {
            return route('admin.super_dashboard');
        }

        // Admin de tenant
        if ($user->role === 'admin') {
            return route('admin.admindashboard');
        }

        // Documentador
        if ($user->role === 'documentador' || $user->role === 'Documentador') {
            return route('documentador.dashboard');
        }

        // Tráfico
        if ($user->role === 'Trafico' || $user->role === 'trafico') {
            return route('trafico.index');
        }

        // Cliente
        if ($user->role === 'cliente' || $user->role === 'Cliente' || $user->role === 'ClienteAdmin') {
            return route('cliente.admindashboard');
        }

        // Finanzas
        if ($user->role === 'finanzas') {
            return route('finanzas.index');
        }

        // Default
        return route('dashboard');
    }
}
