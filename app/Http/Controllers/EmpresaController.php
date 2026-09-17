<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->validate(['q' => 'nullable|string|max:255']);
        $search = trim($data['q'] ?? '');
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }
        return response()->json(Empresa::query()
            ->where(function ($query) use ($search) {
                $query->where('nome', 'like', '%' . $search . '%')
                    ->orWhere('cnpj', 'like', '%' . $search . '%');
            })->orderBy('nome')->limit(20)->get(['id', 'nome', 'cnpj']));
    }

    /* =====================================
       LISTAGEM
       ===================================== */
    public function index(Request $request)
    {
        $empresas = Empresa::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $empresas->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%");
            });
        }

        $empresas = $empresas
            ->orderBy('nome')
            ->paginate(10);

        return view('empresas.index', compact('empresas'));
    }

    /* =====================================
       CREATE
       ===================================== */
    public function create()
    {
        return view('empresas.create');
    }

    /* =====================================
       STORE
       ===================================== */
    public function store(Request $request)
    {
        $this->normalizarDados($request);

        $this->validar($request);

        $empresa = Empresa::create($request->only([
            'nome',
            'tipo',
            'cnpj',
            'telefone',
            'email',
            'endereco_rua',
            'endereco_numero',
            'endereco_bairro',
            'endereco_cidade',
            'endereco_estado',
            'endereco_cep',
        ]));

        $this->uploadDocumentos($empresa, $request);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa cadastrada com sucesso!');
    }

    /* =====================================
       SHOW
       ===================================== */
    public function show(Empresa $empresa)
    {
        $empresa->load('documentos');
        return view('empresas.show', compact('empresa'));
    }

    /* =====================================
       EDIT
       ===================================== */
    public function edit(Empresa $empresa)
    {
        $empresa->load('documentos');
        return view('empresas.edit', compact('empresa'));
    }

    /* =====================================
       UPDATE
       ===================================== */
    public function update(Request $request, Empresa $empresa)
    {
        // The document number is read-only in the edit form.
        $request->merge(['cnpj' => $empresa->cnpj]);
        $this->normalizarDados($request);

        $this->validar($request, $empresa->id);

        $empresa->update($request->only([
            'nome',
            'telefone',
            'email',
            'endereco_rua',
            'endereco_numero',
            'endereco_bairro',
            'endereco_cidade',
            'endereco_estado',
            'endereco_cep',
        ]));

        $this->uploadDocumentos($empresa, $request);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /* =====================================
       EXCLUIR DOCUMENTO
       ===================================== */
    public function deleteDocumento(Empresa $empresa, Documento $documento)
    {
        abort_if($documento->empresa_id !== $empresa->id, 403);

        if (Storage::disk('public')->exists($documento->caminho_arquivo)) {
            Storage::disk('public')->delete($documento->caminho_arquivo);
        }

        $documento->delete();

        return back()->with('success', 'Documento removido com sucesso.');
    }

    public function downloadDocumento(Empresa $empresa, Documento $documento)
    {
        abort_unless((int) $documento->empresa_id === (int) $empresa->id, 404);
        abort_unless(Storage::disk('public')->exists($documento->caminho_arquivo), 404);
        return Storage::disk('public')->download($documento->caminho_arquivo, $documento->nome_arquivo);
    }

    /* =====================================
       DESTROY
       ===================================== */
    public function destroy(Empresa $empresa)
    {
        $funcionarios = $empresa->funcionarios()->pluck('id');
        $empresa->delete();
        Storage::disk('public')->deleteDirectory("empresas/{$empresa->id}");
        foreach ($funcionarios as $id) {
            Storage::disk('public')->deleteDirectory("funcionarios/{$id}");
        }

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa excluída com sucesso!');
    }

    /* =====================================
       MÉTODOS AUXILIARES
       ===================================== */

    private function validar(Request $request, $empresaId = null)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => ['required', 'regex:/^(?:[0-9]{11}|[0-9]{14})$/', 'unique:empresas,cnpj,' . $empresaId],
            'telefone' => 'required|string|max:20',
            'email' => 'required|email',
            'endereco_rua' => 'required|string|max:255',
            'endereco_numero' => 'required|string|max:20',
            'endereco_bairro' => 'required|string|max:255',
            'endereco_cidade' => 'required|string|max:255',
            'endereco_estado' => 'required|string|max:2',
            'endereco_cep' => 'required|string|max:9',
            'documentos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);
    }

    private function normalizarDados(Request $request)
    {
        $request->merge([
            'cnpj' => preg_replace('/\D/', '', $request->cnpj),
            'tipo' => strlen(preg_replace('/\D/', '', $request->cnpj)) === 11 ? 'CPF' : 'CNPJ',
            'endereco_estado' => strtoupper(substr($request->endereco_estado, 0, 2)),
        ]);
    }

    private function uploadDocumentos(Empresa $empresa, Request $request)
    {
        if (!$request->hasFile('documentos')) return;

        foreach ($request->file('documentos') as $file) {
            if (!$file) continue;

            $path = $file->store("empresas/{$empresa->id}", 'public');

            Documento::create([
                'empresa_id' => $empresa->id,
                'nome_arquivo' => $file->getClientOriginalName(),
                'caminho_arquivo' => $path,
                'mime' => $file->getClientMimeType(),
                'tamanho' => $file->getSize(),
            ]);
        }
    }
}
