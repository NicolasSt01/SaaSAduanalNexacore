<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SingleSessionGuard
{
    /**
     * Verifica que la sesión actual del usuario sea la sesión activa registrada.
     * Si otra sesión fue registrada después (login desde otro equipo),
     * se cierra esta sesión y se redirige al login con un aviso.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = session()->getId();

            // Si el usuario tiene una sesión activa registrada y NO coincide con esta
            if ($user->active_session_id && $user->active_session_id !== $currentSessionId) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Tu sesión fue cerrada porque se inició sesión en otro dispositivo.');
            }
        }

        return $next($request);
    }
}
