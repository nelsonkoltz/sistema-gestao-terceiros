<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(fn ($model) => $model->registrarAuditoria('Criado', null, $model->getAttributes()));
        static::updated(function ($model) {
            $alterados = array_keys($model->getChanges());
            $alterados = array_values(array_diff($alterados, ['updated_at']));
            if ($alterados === []) return;
            $antes = array_intersect_key($model->getOriginal(), array_flip($alterados));
            $depois = array_intersect_key($model->getAttributes(), array_flip($alterados));
            $model->registrarAuditoria('Alterado', $antes, $depois);
        });
        static::deleted(fn ($model) => $model->registrarAuditoria('Excluído', $model->getOriginal(), null));
    }

    private function registrarAuditoria(string $acao, ?array $antes, ?array $depois): void
    {
        Auditoria::create([
            'usuario_id' => Auth::id(),
            'modulo' => class_basename($this),
            'acao' => $acao,
            'registro_tipo' => get_class($this),
            'registro_id' => $this->getKey(),
            'dados_anteriores' => $this->protegerDadosAuditoria($antes),
            'dados_novos' => $this->protegerDadosAuditoria($depois),
            'ip' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : mb_substr((string) request()->userAgent(), 0, 1000),
        ]);
    }

    private function protegerDadosAuditoria(?array $dados): ?array
    {
        if ($dados === null) return null;
        foreach (['password', 'remember_token'] as $campo) {
            if (array_key_exists($campo, $dados)) $dados[$campo] = '[PROTEGIDO]';
        }
        if ($this instanceof Configuracao && $this->chave === 'mail_senha' && array_key_exists('valor', $dados)) {
            $dados['valor'] = '[PROTEGIDO]';
        }
        return $dados;
    }
}
