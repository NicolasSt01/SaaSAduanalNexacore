<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
}
