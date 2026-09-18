<?php

namespace App\Http\Middleware;

use Closure;

class EnsureWorkSafety
{
    public function handle($request, Closure $next)
    {
        $usuario = $request->user();
        $autorizado = $usuario && in_array(
            $usuario->permissao,
            ['Administrador', 'Segurança do Trabalho'],
            true
        );

        abort_unless($autorizado, 403, 'Ação restrita à Segurança do Trabalho.');

        return $next($request);
    }
}
