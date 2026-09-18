<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Services\AlertaDocumentoService;
use Illuminate\Http\Request;

class AlertaDocumentoController extends Controller
{
    public function index(Request $request, AlertaDocumentoService $service)
    {
        $dados = $service->dados();
        $itens = $dados['itens'];
        if ($request->filled('tipo')) $itens = $itens->where('tipo', $request->tipo);
        if ($request->filled('situacao')) $itens = $itens->where('situacao', $request->situacao);
        if ($request->filled('busca')) {
            $busca = mb_strtolower(trim((string) $request->busca));
            $itens = $itens->filter(fn ($item) =>
                str_contains(mb_strtolower($item['nome'].' '.$item['empresa'].' '.$item['arquivo']), $busca)
            );
        }
        $destinatarios = $service->destinatarios();
        $ultimoEnvio = Configuracao::valor('ultimo_envio_alertas_documentos');
        $mailer = config('mail.default');

        return view('alertas-documentos.index', $dados + compact('itens', 'destinatarios', 'ultimoEnvio', 'mailer'));
    }

    public function enviarEmails(AlertaDocumentoService $service)
    {
        $total = $service->enviar();
        if (!$total) return back()->with('error', 'Nenhum e-mail foi enviado. Verifique se existem destinatários e se o servidor SMTP está ativo.');
        return back()->with('success', "Resumo enviado para {$total} destinatário(s).");
    }
}
