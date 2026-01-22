<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    // ================================
    // LISTAGEM DE EMPRESAS
    // ================================
    public function index(Request $request)
    {
        $empresas = Empresa::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $empresas->where(function ($query) use ($searchTerm) {
                $query->where('nome', 'like', '%' . $searchTerm . '%')
                      ->orWhere('cnpj', 'like', '%' . $searchTerm . '%');
            });
        }

        $empresas = $empresas->paginate(10);

        return view('empresas.index', compact('empresas'));
    }

    // ================================
    // CRIAR NOVA EMPRESA
    // ================================
    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        // Limpa CNPJ/CPF (remove caracteres não numéricos)
        $cnpjOuCpf = preg_replace('/\D/', '', $request->cnpj);
        $request->merge(['cnpj' => $cnpjOuCpf]);

        // Validação dos campos
        $rules = [
            'nome' => 'required|string|max:255',
            'cnpj' => [
                'required',
                'string',
                'min:11',
                'max:14',
                'regex:/^\d{11,14}$/',
                'unique:empresas,cnpj',
            ],
            'telefone' => 'required|string|max:20',
            'email' => 'required|email',
            'endereco_rua' => 'required|string|max:255',
            'endereco_numero' => 'required|string|max:20',
            'endereco_bairro' => 'required|string|max:255',
            'endereco_cidade' => 'required|string|max:255',
            'endereco_estado' => 'required|string|max:2',
            'endereco_cep' => 'required|string|max:9',
            'documentos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480', // 20MB
        ];

        $request->validate($rules, [
            'cnpj.regex' => 'O CNPJ/CPF deve conter apenas números (11 ou 14 dígitos).',
            'cnpj.unique' => 'Já existe uma empresa cadastrada com este CNPJ/CPF.',
        ]);

        // Criação da empresa
        $empresa = Empresa::create($request->only([
            'nome',
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

        // Upload dos documentos (se houver)
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                if (!$file) continue;

                $path = $file->store("empresas/{$empresa->id}", 'public');

                Documento::create([
                    'empresa_id' => $empresa->id, // ✅ vincula corretamente
                    'nome_arquivo' => $file->getClientOriginalName(),
                    'caminho_arquivo' => $path,
                    'mime' => $file->getClientMimeType(),
                    'tamanho' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa cadastrada com sucesso!');
    }

    // ================================
    // EXIBIR DETALHES DA EMPRESA
    // ================================
    public function show($id)
    {
        $empresa = Empresa::with('documentos')->findOrFail($id);
        return view('empresas.show', compact('empresa'));
    }

    // ================================
    // EDITAR EMPRESA
    // ================================
    public function edit($id)
    {
        $empresa = Empresa::with('documentos')->findOrFail($id);
        return view('empresas.edit', compact('empresa'));
    }

    // ================================
    // ATUALIZAR EMPRESA
    // ================================
    public function update(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        // Validação dos campos
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email',
            'telefone' => 'required|string|max:20',
            'endereco_rua' => 'required|string|max:255',
            'endereco_numero' => 'required|string|max:20',
            'endereco_bairro' => 'required|string|max:255',
            'endereco_cidade' => 'required|string|max:255',
            'endereco_estado' => 'required|string|max:2',
            'endereco_cep' => 'required|string|max:9',
            'documentos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        // Atualiza dados básicos
        $empresa->update($request->only([
            'nome',
            'email',
            'telefone',
            'endereco_rua',
            'endereco_numero',
            'endereco_bairro',
            'endereco_cidade',
            'endereco_estado',
            'endereco_cep',
        ]));

        // Upload de novos documentos (se houver)
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                if (!$file) continue;

                $path = $file->store("empresas/{$empresa->id}", 'public');

                Documento::create([
                    'empresa_id' => $empresa->id, // ✅ vincula corretamente
                    'nome_arquivo' => $file->getClientOriginalName(),
                    'caminho_arquivo' => $path,
                    'mime' => $file->getClientMimeType(),
                    'tamanho' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    // ================================
    // EXCLUIR DOCUMENTO INDIVIDUAL
    // ================================
    public function deleteDocumento($empresaId, $documentoId)
    {
        $documento = Documento::where('empresa_id', $empresaId)->findOrFail($documentoId);

        // Remove o arquivo físico
        if ($documento->caminho_arquivo && Storage::disk('public')->exists($documento->caminho_arquivo)) {
            Storage::disk('public')->delete($documento->caminho_arquivo);
        }

        // Remove do banco
        $documento->delete();

        return redirect()->back()->with('success', 'Documento removido com sucesso.');
    }

    // ================================
    // EXCLUIR EMPRESA (com documentos)
    // ================================
    public function destroy($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete(); // ✅ onDelete('cascade') cuida dos documentos

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa excluída com sucesso!');
    }

    // ================================
    // AUTOCOMPLETE DE EMPRESAS
    // ================================
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if ($q === '') return response()->json([]);

        $empresas = Empresa::where('nome', 'like', "%{$q}%")
            ->orderBy('nome')
            ->limit(10)
            ->get(['id', 'nome']);

        return response()->json($empresas);
    }
}
