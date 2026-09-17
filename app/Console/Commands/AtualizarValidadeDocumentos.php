<?php

namespace App\Console\Commands;

use App\Models\Configuracao;
use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use Illuminate\Console\Command;

class AtualizarValidadeDocumentos extends Command
{
    protected $signature = 'documentos:atualizar-validade {--force : Executa mesmo com o job desativado}';
    protected $description = 'Marca como vencidos os documentos cuja validade de seis meses terminou';

    public function handle(): int
    {
        if (!$this->option('force') && !Configuracao::booleano('job_validade_documentos_ativo', true)) {
            $this->info('Job de validade desativado nas configurações.');
            return self::SUCCESS;
        }

        $empresa = Documento::whereIn('status', ['Aprovado', 'Proximo do vencimento'])
            ->whereDate('validade_ate', '<', today())->update(['status' => 'Vencido']);
        $funcionario = DocumentoFuncionario::whereIn('status', ['Aprovado', 'Proximo do vencimento'])
            ->whereDate('validade_ate', '<', today())->update(['status' => 'Vencido']);

        Documento::where('status', 'Aprovado')->whereBetween('validade_ate', [today(), today()->addDays(30)])
            ->update(['status' => 'Proximo do vencimento']);
        DocumentoFuncionario::where('status', 'Aprovado')->whereBetween('validade_ate', [today(), today()->addDays(30)])
            ->update(['status' => 'Proximo do vencimento']);

        $this->info(($empresa + $funcionario) . ' documento(s) marcado(s) como vencido(s).');
        return self::SUCCESS;
    }
}
