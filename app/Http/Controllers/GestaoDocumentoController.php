<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GestaoDocumentoController extends Controller
{
    public function storeEmpresa(Request $request, Empresa $empresa)
    {
        $documento = $this->salvar($request, $empresa, Documento::class, 'empresa_id', "empresas/{$empresa->id}");
        return back()->with('success', 'Documento cadastrado. Validade até '.$documento->validade_ate->format('d/m/Y').'.');
    }

    public function storeFuncionario(Request $request, Funcionario $funcionario)
    {
        $documento = $this->salvar($request, $funcionario, DocumentoFuncionario::class, 'funcionario_id', "funcionarios/{$funcionario->id}");
        return back()->with('success', 'Documento cadastrado. Validade até '.$documento->validade_ate->format('d/m/Y').'.');
    }

    private function salvar(Request $request, Model $dono, string $classe, string $chave, string $diretorio): Model
    {
        $data = $this->validarUpload($request);
        $path = null;
        try {
            return DB::transaction(function () use ($request, $dono, $classe, $chave, $diretorio, $data, &$path) {
                $dono->newQuery()->whereKey($dono->id)->lockForUpdate()->firstOrFail();
                $anterior = isset($data['documento_anterior_id'])
                    ? $classe::where($chave, $dono->id)->lockForUpdate()->findOrFail($data['documento_anterior_id']) : null;
                if ($anterior && ($anterior->status === 'Substituido' || $anterior->posteriores()->exists())) {
                    throw ValidationException::withMessages(['arquivo' => 'Este documento já foi renovado. Atualize a página e renove a versão atual.']);
                }
                $arquivo = $request->file('arquivo');
                $path = $arquivo->store($diretorio, 'public');
                if (!$path) throw new \RuntimeException('Não foi possível armazenar o documento.');
                $atributos = $classe === Documento::class
                    ? ['nome_arquivo' => mb_substr($arquivo->getClientOriginalName(), 0, 255), 'caminho_arquivo' => $path]
                    : ['nome_original' => mb_substr($arquivo->getClientOriginalName(), 0, 255), 'path' => $path,
                        'mime' => $arquivo->getMimeType(), 'tamanho' => $arquivo->getSize()];
                $documento = $classe::create($atributos + [$chave => $dono->id, 'documento_anterior_id' => $anterior?->id]);
                if ($anterior) $anterior->update(['status' => 'Substituido']);
                return $documento;
            });
        } catch (\Throwable $e) {
            if ($path) Storage::disk('public')->delete($path);
            throw $e;
        }
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
