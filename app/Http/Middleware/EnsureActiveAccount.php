<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureActiveAccount
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->ativo === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['username' => 'Sua conta está inativa. Procure o administrador.']);
        }
        return $next($request);
    }
}
