<?php

namespace App\Http\Middleware;

use Closure;

class EnsureWritePermission
{
    public function handle($request, Closure $next)
    {
        $writing = !$request->isMethodSafe() || $request->routeIs('*.create', '*.edit');
        if ($writing && $request->user()->permissao === 'Consulta') {
            abort(403, 'Seu perfil permite somente consulta.');
        }
        return $next($request);
    }
}
