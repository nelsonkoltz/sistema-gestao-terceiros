<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class GestaoDocumentoController extends Controller
{
    public function storeEmpresa(Request $request, Empresa $empresa)
    {
        $data = $this->validarUpload($request);
        $anterior = isset($data['documento_anterior_id'])
            ? Documento::where('empresa_id', $empresa->id)->findOrFail($data['documento_anterior_id']) : null;
        $path = $request->file('arquivo')->store("empresas/{$empresa->id}", 'public');
        $documento = Documento::create([
            'empresa_id' => $empresa->id,
            'nome_arquivo' => mb_substr($request->file('arquivo')->getClientOriginalName(), 0, 255),
            'caminho_arquivo' => $path,
            'documento_anterior_id' => $data['documento_anterior_id'] ?? null,
        ]);
        if ($anterior) $anterior->update(['status' => 'Substituido']);
        return back()->with('success', 'Documento enviado para análise. Validade: seis meses a partir de hoje.');
    }

    public function storeFuncionario(Request $request, Funcionario $funcionario)
    {
        $data = $this->validarUpload($request);
        $anterior = isset($data['documento_anterior_id'])
            ? DocumentoFuncionario::where('funcionario_id', $funcionario->id)->findOrFail($data['documento_anterior_id']) : null;
        $arquivo = $request->file('arquivo');
        $path = $arquivo->store("funcionarios/{$funcionario->id}", 'public');
        $documento = DocumentoFuncionario::create([
            'funcionario_id' => $funcionario->id,
            'nome_original' => mb_substr($arquivo->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime' => $arquivo->getMimeType(),
            'tamanho' => $arquivo->getSize(),
            'documento_anterior_id' => $data['documento_anterior_id'] ?? null,
        ]);
        if ($anterior) $anterior->update(['status' => 'Substituido']);
        return back()->with('success', 'Documento enviado para análise. Validade: seis meses a partir de hoje.');
    }

    public function analisarEmpresa(Request $request, Empresa $empresa, Documento $documento)
    {
        abort_unless((int) $documento->empresa_id === (int) $empresa->id, 404);
        $this->analisar($request, $documento);
        return back()->with('success', 'Análise do documento registrada.');
    }

    public function analisarFuncionario(Request $request, Funcionario $funcionario, DocumentoFuncionario $documento)
    {
        abort_unless((int) $documento->funcionario_id === (int) $funcionario->id, 404);
        $this->analisar($request, $documento);
        return back()->with('success', 'Análise do documento registrada.');
    }

    private function analisar(Request $request, $documento): void
    {
        $data = $request->validate([
            'status' => 'required|in:Aprovado,Rejeitado',
            'observacao_analise' => 'nullable|string|max:1000|required_if:status,Rejeitado',
        ]);
        $documento->update([
            'status' => $data['status'],
            'observacao_analise' => $data['observacao_analise'] ?? null,
            'analisado_por' => $request->user()->id,
            'analisado_em' => now(),
        ]);
    }

    private function validarUpload(Request $request): array
    {
        $data = $request->validate([
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'documento_anterior_id' => 'nullable|integer',
        ]);
        return $data;
    }
}
