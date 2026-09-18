<?php

namespace App\Console\Commands;

use App\Models\Configuracao;
use App\Services\AlertaDocumentoService;
use Illuminate\Console\Command;

class EnviarAlertasDocumentos extends Command
{
    protected $signature = 'documentos:enviar-alertas {--force}';
    protected $description = 'Envia por e-mail o resumo de documentos vencidos e próximos do vencimento';

    public function handle(AlertaDocumentoService $service): int
    {
        if (!$this->option('force') && !Configuracao::booleano('job_validade_documentos_ativo', true)) {
            $this->info('Job de documentos desativado.');
            return self::SUCCESS;
        }
        $total = $service->enviar();
        $this->info("Alertas enviados para {$total} destinatário(s).");
        return self::SUCCESS;
    }
}
