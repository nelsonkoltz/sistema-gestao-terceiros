<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Services\AlertaDocumentoService;
use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use Illuminate\Support\Facades\DB;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $jobAtivo = Configuracao::booleano('job_validade_documentos_ativo', true);
        $email = [
            'ativo' => Configuracao::booleano('mail_ativo', false),
            'host' => Configuracao::valor('mail_host', 'smtp.office365.com'),
            'porta' => Configuracao::valor('mail_porta', '587'),
            'criptografia' => Configuracao::valor('mail_criptografia', 'tls'),
            'usuario' => Configuracao::valor('mail_usuario'),
            'remetente_email' => Configuracao::valor('mail_remetente_email'),
            'remetente_nome' => Configuracao::valor('mail_remetente_nome', 'TerceirosCR'),
            'possui_senha' => (bool) Configuracao::valor('mail_senha'),
        ];
        $alertas = [
            'critico' => Configuracao::valor('alerta_dias_critico', 7),
            'atencao' => Configuracao::valor('alerta_dias_atencao', 15),
            'preventivo' => Configuracao::valor('alerta_dias_preventivo', 30),
        ];
        $validade = [
            'quantidade' => Configuracao::valor('validade_documentos_quantidade', Configuracao::valor('validade_documentos_meses', 6)),
            'unidade' => Configuracao::valor('validade_documentos_unidade', 'meses'),
        ];
        return view('configuracoes.index', compact('jobAtivo', 'email', 'alertas', 'validade'));
    }

    public function updateJob(Request $request)
    {
        Configuracao::updateOrCreate(['chave' => 'job_validade_documentos_ativo'], [
            'valor' => $request->boolean('ativo') ? '1' : '0',
        ]);
        return back()->with('success', 'Configuração do job atualizada.');
    }

    public function executarJob()
    {
        Artisan::call('documentos:atualizar-validade', ['--force' => true]);
        return back()->with('success', trim(Artisan::output()) ?: 'Verificação executada.');
    }

    public function updateEmail(Request $request)
    {
        $data = $request->validate([
            'ativo' => 'required|boolean',
            'host' => 'required|string|max:255',
            'porta' => 'required|integer|min:1|max:65535',
            'criptografia' => 'required|in:tls,ssl',
            'usuario' => 'required|email|max:255',
            'senha' => 'nullable|string|max:500',
            'remetente_email' => 'required|email|max:255',
            'remetente_nome' => 'required|string|max:100',
        ]);
        if (!$request->filled('senha') && !Configuracao::valor('mail_senha')) {
            return back()->withErrors(['senha' => 'Informe a senha ou senha de aplicativo da conta Outlook.']);
        }
        $valores = [
            'mail_ativo' => $request->boolean('ativo') ? '1' : '0',
            'mail_host' => $data['host'], 'mail_porta' => $data['porta'],
            'mail_criptografia' => $data['criptografia'], 'mail_usuario' => $data['usuario'],
            'mail_remetente_email' => $data['remetente_email'],
            'mail_remetente_nome' => $data['remetente_nome'],
        ];
        if ($request->filled('senha')) $valores['mail_senha'] = Crypt::encryptString($data['senha']);
        foreach ($valores as $chave => $valor) Configuracao::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
        return back()->with('success', 'Configuração do Outlook salva com segurança.');
    }

    public function testarEmail(Request $request, AlertaDocumentoService $service)
    {
        if (!$request->user()->email) return back()->withErrors(['email' => 'Cadastre seu e-mail no usuário antes de testar.']);
        if (!$service->configurarEmail()) return back()->withErrors(['email' => 'Ative e salve a configuração do Outlook antes do teste.']);
        try {
            Mail::raw('O envio de e-mails do TerceirosCR foi configurado corretamente.', function ($mensagem) use ($request) {
                $mensagem->to($request->user()->email, $request->user()->name)->subject('✅ Teste de e-mail — TerceirosCR');
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['email' => 'Não foi possível enviar pelo servidor SMTP. Confira o endereço do servidor, porta, criptografia, usuário e senha.']);
        }
        return back()->with('success', 'E-mail de teste enviado para '.$request->user()->email.'.');
    }

    public function updateAlertas(Request $request)
    {
        $data = $request->validate([
            'critico' => 'required|integer|min:1|max:365',
            'atencao' => 'required|integer|gt:critico|max:365',
            'preventivo' => 'required|integer|gt:atencao|max:365',
        ]);
        foreach ($data as $nome => $valor) {
            Configuracao::updateOrCreate(['chave' => 'alerta_dias_'.$nome], ['valor' => $valor]);
        }
        return back()->with('success', 'Prazos dos alertas documentais atualizados.');
    }

    public function updateValidade(Request $request)
    {
        $data = $request->validate([
            'quantidade' => 'required|integer|min:1|max:3650',
            'unidade' => 'required|in:dias,meses',
            'recalcular_existentes' => 'nullable|boolean',
        ]);
        Configuracao::updateOrCreate(['chave' => 'validade_documentos_quantidade'], ['valor' => $data['quantidade']]);
        Configuracao::updateOrCreate(['chave' => 'validade_documentos_unidade'], ['valor' => $data['unidade']]);

        $recalculados = 0;
        if ($request->boolean('recalcular_existentes')) {
            DB::transaction(function () use ($data, &$recalculados) {
                foreach ([Documento::class, DocumentoFuncionario::class] as $model) {
                    $model::where('status', '!=', 'Substituido')->chunkById(100, function ($documentos) use ($data, &$recalculados) {
                        foreach ($documentos as $documento) {
                            $base = $documento->created_at->copy();
                            $validade = $data['unidade'] === 'dias'
                                ? $base->addDays($data['quantidade'])
                                : $base->addMonthsNoOverflow($data['quantidade']);
                            $documento->update([
                                'validade_ate' => $validade->toDateString(),
                                'status' => $validade->lte(today()) ? 'Vencido' : 'Ativo',
                            ]);
                            $recalculados++;
                        }
                    });
                }
            });
            Artisan::call('documentos:atualizar-validade', ['--force' => true]);
        }

        $mensagem = 'Prazo de validade atualizado.';
        if ($recalculados) $mensagem .= " {$recalculados} documento(s) existente(s) recalculado(s).";
        else $mensagem .= ' A nova regra será aplicada aos próximos documentos e renovações.';
        return back()->with('success', $mensagem);
    }

}
