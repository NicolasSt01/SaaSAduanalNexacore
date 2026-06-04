<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->tenant && $user->tenant->isSuspended() && !$user->isSuperAdmin()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu agencia ha sido suspendida. Contacta a soporte para más información.');
        }

        return $next($request);
    }
}
