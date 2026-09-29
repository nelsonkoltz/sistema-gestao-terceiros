<?php

namespace App\Http\Middleware;

use App\Models\Filial;
use Closure;
use Illuminate\Http\Request;

class ResolveFilial
{
    public function handle(Request $request, Closure $next)
    {
        if (!$usuario = $request->user()) return $next($request);

        $filiais = $usuario->permissao === 'Administrador'
            ? Filial::orderByDesc('ativa')->orderBy('nome')->get()
            : $usuario->filiais()->where('ativa', true)->orderByPivot('principal', 'desc')->orderBy('nome')->get();
        $filial = $filiais->firstWhere('id', (int) $request->session()->get('filial_ativa_id')) ?? $filiais->first();
        $request->session()->put('filial_ativa_id', $filial?->id);
        $request->attributes->set('filial_ativa_id', $filial?->id ?? 0);
        $request->attributes->set('filiais_disponiveis', $filiais);

        if ($request->routeIs('servicos.*', 'guarita.*')) {
            abort_unless($filial, 403, 'Sua conta não possui uma filial ativa. Solicite o vínculo ao administrador.');
            abort_if(!$filial->ativa && !$request->isMethodSafe(), 403, 'Esta filial está inativa e permite apenas consulta ao histórico.');
        }

        return $next($request);
    }
}
