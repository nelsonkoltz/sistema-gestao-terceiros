<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $jobAtivo = Configuracao::booleano('job_validade_documentos_ativo', true);
        return view('configuracoes.index', compact('jobAtivo'));
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

}
