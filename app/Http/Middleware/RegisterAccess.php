<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class RegisterAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Permitir registro si no hay usuarios
        if (User::count() === 0) {
            return $next($request);
        }

        // Permitir solo a administradores
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Acceso al registro no autorizado');
    }
}