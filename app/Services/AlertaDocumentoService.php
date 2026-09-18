<?php

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class AlertaDocumentoService
{
    public function dados(): array
    {
        $dias = $this->diasAlertas();
        $documentosEmpresa = Documento::with('empresa')
            ->where('status', '!=', 'Substituido')->get();
        $documentosFuncionario = DocumentoFuncionario::with('funcionario.empresa')
            ->where('status', '!=', 'Substituido')->get();

        $itens = collect();
        foreach ($documentosEmpresa as $documento) {
            $situacao = $this->situacao($documento->validade_ate, $dias);
            if ($situacao) $itens->push([
                'tipo' => 'Empresa', 'situacao' => $situacao,
                'nome' => optional($documento->empresa)->nome ?? 'Empresa removida',
                'empresa' => optional($documento->empresa)->nome ?? '—',
                'arquivo' => $documento->nome_arquivo,
                'validade' => $documento->validade_ate,
                'url' => $documento->empresa ? route('empresas.show', $documento->empresa) : null,
            ]);
        }
        foreach ($documentosFuncionario as $documento) {
            $situacao = $this->situacao($documento->validade_ate, $dias);
            if ($situacao) $itens->push([
                'tipo' => 'Funcionário', 'situacao' => $situacao,
                'nome' => optional($documento->funcionario)->nome ?? 'Funcionário removido',
                'empresa' => optional(optional($documento->funcionario)->empresa)->nome ?? '—',
                'arquivo' => $documento->nome_original,
                'validade' => $documento->validade_ate,
                'url' => $documento->funcionario ? route('funcionarios.show', $documento->funcionario) : null,
            ]);
        }

        Empresa::whereDoesntHave('documentos', fn ($q) => $q->where('status', '!=', 'Substituido'))
            ->get()->each(fn ($empresa) => $itens->push([
                'tipo' => 'Empresa', 'situacao' => 'Sem documentos', 'nome' => $empresa->nome,
                'empresa' => $empresa->nome, 'arquivo' => 'Nenhum documento cadastrado',
                'validade' => null, 'url' => route('empresas.show', $empresa),
            ]));

        Funcionario::with('empresa')->where('ativo', true)
            ->whereDoesntHave('documentos', fn ($q) => $q->where('status', '!=', 'Substituido'))
            ->get()->each(fn ($funcionario) => $itens->push([
                'tipo' => 'Funcionário', 'situacao' => 'Sem documentos', 'nome' => $funcionario->nome,
                'empresa' => optional($funcionario->empresa)->nome ?? '—',
                'arquivo' => 'Nenhum documento cadastrado', 'validade' => null,
                'url' => route('funcionarios.show', $funcionario),
            ]));

        $itens = $itens->sortBy(fn ($item) => ($item['validade']?->format('Ymd') ?? '00000000'))->values();
        return [
            'itens' => $itens,
            'vencidos' => $itens->where('situacao', 'Vencido')->count(),
            'vencendo7' => $itens->where('situacao', "Vence em até {$dias['critico']} dias")->count(),
            'vencendo15' => $itens->where('situacao', "Vence em até {$dias['atencao']} dias")->count(),
            'vencendo30' => $itens->where('situacao', "Vence em até {$dias['preventivo']} dias")->count(),
            'semDocumentos' => $itens->where('situacao', 'Sem documentos')->count(),
            'diasAlertas' => $dias,
        ];
    }

    public function destinatarios()
    {
        return Usuario::whereIn('permissao', ['Administrador', 'Segurança do Trabalho'])
            ->whereNotNull('email')->where('email', '!=', '')->orderBy('name')->get();
    }

    public function enviar(): int
    {
        if (!$this->configurarEmail()) return 0;
        $dados = $this->dados();
        $destinatarios = $this->destinatarios();
        if ($destinatarios->isEmpty()) return 0;

        foreach ($destinatarios as $destinatario) {
            Mail::send('emails.alertas-documentos', $dados + ['destinatario' => $destinatario], function ($mensagem) use ($destinatario) {
                $mensagem->to($destinatario->email, $destinatario->name)
                    ->subject('🔔 TerceirosCR — Alertas de documentos');
            });
        }
        Configuracao::updateOrCreate(['chave' => 'ultimo_envio_alertas_documentos'], ['valor' => now()->toDateTimeString()]);
        return $destinatarios->count();
    }

    public function configurarEmail(): bool
    {
        if (!Configuracao::booleano('mail_ativo', false)) return false;
        $senhaCriptografada = Configuracao::valor('mail_senha');
        if (!$senhaCriptografada) return false;
        try {
            $senha = Crypt::decryptString($senhaCriptografada);
        } catch (\Throwable $e) {
            return false;
        }
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => Configuracao::valor('mail_host', 'smtp.office365.com'),
            'mail.mailers.smtp.port' => (int) Configuracao::valor('mail_porta', 587),
            'mail.mailers.smtp.encryption' => Configuracao::valor('mail_criptografia', 'tls'),
            'mail.mailers.smtp.username' => Configuracao::valor('mail_usuario'),
            'mail.mailers.smtp.password' => $senha,
            'mail.from.address' => Configuracao::valor('mail_remetente_email'),
            'mail.from.name' => Configuracao::valor('mail_remetente_nome', 'TerceirosCR'),
        ]);
        return true;
    }

    public function diasAlertas(): array
    {
        return [
            'critico' => (int) Configuracao::valor('alerta_dias_critico', 7),
            'atencao' => (int) Configuracao::valor('alerta_dias_atencao', 15),
            'preventivo' => (int) Configuracao::valor('alerta_dias_preventivo', 30),
        ];
    }

    private function situacao($validade, array $diasAlerta): ?string
    {
        if (!$validade) return 'Vencido';
        if ($validade->lt(today())) return 'Vencido';
        $dias = today()->diffInDays($validade, false);
        if ($dias <= $diasAlerta['critico']) return "Vence em até {$diasAlerta['critico']} dias";
        if ($dias <= $diasAlerta['atencao']) return "Vence em até {$diasAlerta['atencao']} dias";
        if ($dias <= $diasAlerta['preventivo']) return "Vence em até {$diasAlerta['preventivo']} dias";
        return null;
    }
}
