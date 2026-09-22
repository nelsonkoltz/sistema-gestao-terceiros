<?php

namespace App\Http\Middleware;

use App\Models\Auditoria;
use App\Models\Configuracao;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) return $next($request);

        $limite = max(1, (int) Configuracao::valor('sessao_inatividade_minutos', 30));
        $ultimaAtividade = (int) $request->session()->get('ultima_atividade_em', now()->timestamp);

        if (now()->timestamp - $ultimaAtividade > $limite * 60) {
            $usuario = $request->user();
            Auditoria::create([
                'usuario_id' => $usuario->id,
                'modulo' => 'Autenticação',
                'acao' => 'Sessão expirada',
                'registro_tipo' => Usuario::class,
                'registro_id' => $usuario->id,
                'dados_novos' => ['limite_inatividade_minutos' => $limite],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', "Sua sessão expirou após {$limite} minutos de inatividade. Entre novamente.");
        }

        $request->session()->put('ultima_atividade_em', now()->timestamp);
        return $next($request);
    }
}
