<?php

namespace App\Console\Commands;

use App\Models\RecuperacaoSenha;
use Illuminate\Console\Command;

class LimparRecuperacoesSenha extends Command
{
    protected $signature = 'senhas:limpar-recuperacoes';
    protected $description = 'Remove tokens de recuperação de senha expirados ou já utilizados';

    public function handle(): int
    {
        $removidos = RecuperacaoSenha::where('expira_em', '<', now())
            ->orWhereNotNull('utilizado_em')
            ->delete();
        $this->info("{$removidos} token(s) de recuperação removido(s).");
        return self::SUCCESS;
    }
}
