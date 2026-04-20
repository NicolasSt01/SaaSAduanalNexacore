<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
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
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
