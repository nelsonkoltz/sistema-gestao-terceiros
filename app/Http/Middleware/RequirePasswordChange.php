<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RequirePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->trocar_senha && !$request->routeIs('minha-conta.*', 'logout')) {
            return redirect()->route('minha-conta.index')
                ->with('warning', 'Troque a senha provisória para continuar utilizando o sistema.');
        }
        return $next($request);
    }
}
