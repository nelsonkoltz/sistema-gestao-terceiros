<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\DocumentoFuncionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FuncionarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =============================
    // LISTAGEM
    // =============================
    public function index()
    {
        $funcionarios = Funcionario::with('empresa')->latest()->paginate(10);
        return view('funcionarios.index', compact('funcionarios'));
    }

    // =============================
    // TELA DE CADASTRO
    // =============================
    public function create()
    {
        $empresas = Empresa::orderBy('nome')->get();
        return view('funcionarios.create', compact('empresas'));
    }

    // =============================
    // SALVAR NOVO FUNCIONÁRIO
    // =============================
    public function store(Request $request)
    {
        // Normaliza CPF e campo ativo
        $request->merge([
            'cpf' => preg_replace('/\D/', '', $request->cpf),
            'ativo' => $request->has('ativo'),
        ]);

        $data = $request->validate([
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'empresa_nome' => ['required', 'string', 'max:255'],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'size:11', 'unique:funcionarios,cpf'],
            'ativo' => ['boolean'],
            'documentos.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Busca a empresa pelo nome caso não tenha ID
        $empresaId = $data['empresa_id'] ?? Empresa::where('nome', $data['empresa_nome'])->value('id');

        if (!$empresaId) {
            return back()
                ->withErrors(['empresa_nome' => 'Empresa não encontrada. Selecione uma da lista.'])
                ->withInput();
        }

        // Criação do funcionário
        $funcionario = Funcionario::create([
            'empresa_id' => $empresaId,
            'nome' => $data['nome'],
            'cpf' => $data['cpf'],
            'ativo' => $data['ativo'],
        ]);

        // Upload de documentos
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                if (!$file) continue;

                $path = $file->store("funcionarios/{$funcionario->id}", 'public');

                DocumentoFuncionario::create([
                    'funcionario_id' => $funcionario->id,
                    'nome_original' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'tamanho' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    // =============================
    // EDITAR FUNCIONÁRIO
    // =============================
    public function edit(Funcionario $funcionario)
    {
        $funcionario->load('empresa', 'documentos');
        $empresas = Empresa::orderBy('nome')->get();

        return view('funcionarios.edit', compact('funcionario', 'empresas'));
    }

    // =============================
    // ATUALIZAR FUNCIONÁRIO
    // =============================
    public function update(Request $request, Funcionario $funcionario)
    {
        $request->merge([
            'cpf' => preg_replace('/\D/', '', $request->cpf),
            'ativo' => $request->has('ativo'),
        ]);

        $data = $request->validate([
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'empresa_nome' => ['required', 'string', 'max:255'],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'size:11', 'unique:funcionarios,cpf,' . $funcionario->id],
            'ativo' => ['boolean'],
            'documentos.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $empresaId = $data['empresa_id'] ?? Empresa::where('nome', $data['empresa_nome'])->value('id');

        if (!$empresaId) {
            return back()
                ->withErrors(['empresa_nome' => 'Empresa não encontrada.'])
                ->withInput();
        }

        $funcionario->update([
            'empresa_id' => $empresaId,
            'nome' => $data['nome'],
            'cpf' => $data['cpf'],
            'ativo' => $data['ativo'],
        ]);

        // Upload de novos documentos
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                if (!$file) continue;

                $path = $file->store("funcionarios/{$funcionario->id}", 'public');

                DocumentoFuncionario::create([
                    'funcionario_id' => $funcionario->id,
                    'nome_original' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'tamanho' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('funcionarios.edit', $funcionario)
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    // =============================
    // EXCLUIR FUNCIONÁRIO
    // =============================
    public function destroy(Funcionario $funcionario)
    {
        // A FK com cascade já apaga os documentos automaticamente
        $funcionario->delete();

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário e documentos removidos com sucesso.');
    }

    // =============================
    // EXCLUIR DOCUMENTO
    // =============================
    public function destroyDocumento(Funcionario $funcionario, DocumentoFuncionario $documento)
    {
        // Garante que o documento pertence ao funcionário correto
        if ($documento->funcionario_id !== $funcionario->id) {
            abort(404, 'Documento não pertence a este funcionário.');
        }

        // Exclui o arquivo físico
        if ($documento->path && Storage::disk('public')->exists($documento->path)) {
            Storage::disk('public')->delete($documento->path);
        }

        // Exclui o registro do banco
        $documento->delete();

        return back()->with('success', 'Documento removido com sucesso.');
    }

    // =============================
    // EXIBIR DETALHES
    // =============================
    public function show(Funcionario $funcionario)
    {
        $funcionario->load('empresa', 'documentos');
        return view('funcionarios.show', compact('funcionario'));
    }
}
