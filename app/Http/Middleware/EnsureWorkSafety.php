<?php

namespace App\Http\Middleware;

use Closure;

class EnsureWorkSafety
{
    public function handle($request, Closure $next)
    {
        $usuario = $request->user();
        $setor = mb_strtolower(trim((string) $usuario->setor));
        $autorizado = $usuario->permissao === 'Administrador'
            || in_array($setor, ['segurança do trabalho', 'seguranca do trabalho'], true);

        abort_unless($autorizado, 403, 'Ação restrita à Segurança do Trabalho.');

        return $next($request);
    }
}
