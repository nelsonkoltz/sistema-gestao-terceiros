<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\DocumentoFuncionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FuncionarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =============================
    // LISTAGEM + BUSCA
    // =============================
    public function index(Request $request)
    {
        $query = Funcionario::with('empresa')->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhereHas('empresa', function ($e) use ($search) {
                      $e->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        $funcionarios = $query
            ->paginate(10)
            ->withQueryString();

        return view('funcionarios.index', compact('funcionarios'));
    }

    // =============================
    // TELA DE CADASTRO
    // =============================
    public function create()
    {
        return view('funcionarios.create');
    }

    // =============================
    // SALVAR NOVO FUNCIONÁRIO
    // =============================
    public function store(Request $request)
    {
        $request->merge([
            'cpf'   => preg_replace('/\D/', '', $request->cpf),
            'ativo' => $request->boolean('ativo'),
        ]);

        $data = $request->validate([
            'empresa_id'   => ['required', 'exists:empresas,id'],
            'nome'         => ['required', 'string', 'max:255'],
            'cpf'          => ['required', 'string', 'size:11', 'unique:funcionarios,cpf'],
            'ativo'        => ['boolean'],
            'documentos.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $data) {

            $funcionario = Funcionario::create([
                'empresa_id' => $data['empresa_id'],
                'nome'       => $data['nome'],
                'cpf'        => $data['cpf'],
                'ativo'      => $data['ativo'],
            ]);

            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $file) {
                    if (!$file) continue;

                    $path = $file->store(
                        "funcionarios/{$funcionario->id}",
                        'public'
                    );

                    DocumentoFuncionario::create([
                        'funcionario_id' => $funcionario->id,
                        'nome_original'  => $file->getClientOriginalName(),
                        'path'           => $path,
                        'mime'           => $file->getClientMimeType(),
                        'tamanho'        => $file->getSize(),
                    ]);
                }
            }
        });

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

        return view('funcionarios.edit', compact('funcionario'));
    }

    // =============================
    // ATUALIZAR FUNCIONÁRIO
    // =============================
    public function update(Request $request, Funcionario $funcionario)
    {
        $request->merge([
            'cpf'   => preg_replace('/\D/', '', $request->cpf),
            'ativo' => $request->boolean('ativo'),
        ]);

        $data = $request->validate([
            'empresa_id'   => ['required', 'exists:empresas,id'],
            'nome'         => ['required', 'string', 'max:255'],
            'cpf'          => ['required', 'string', 'size:11', 'unique:funcionarios,cpf,' . $funcionario->id],
            'ativo'        => ['boolean'],
            'documentos.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $data, $funcionario) {

            $funcionario->update([
                'empresa_id' => $data['empresa_id'],
                'nome'       => $data['nome'],
                'cpf'        => $data['cpf'],
                'ativo'      => $data['ativo'],
            ]);

            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $file) {
                    if (!$file) continue;

                    $path = $file->store(
                        "funcionarios/{$funcionario->id}",
                        'public'
                    );

                    DocumentoFuncionario::create([
                        'funcionario_id' => $funcionario->id,
                        'nome_original'  => $file->getClientOriginalName(),
                        'path'           => $path,
                        'mime'           => $file->getClientMimeType(),
                        'tamanho'        => $file->getSize(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('funcionarios.edit', $funcionario)
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    // =============================
    // EXCLUIR FUNCIONÁRIO
    // =============================
    public function destroy(Funcionario $funcionario)
    {
        $funcionario->delete();

        Storage::disk('public')
            ->deleteDirectory("funcionarios/{$funcionario->id}");

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário removido com sucesso!');
    }

    // =============================
    // EXCLUIR DOCUMENTO
    // =============================
    public function destroyDocumento(Funcionario $funcionario, DocumentoFuncionario $documento)
    {
        if ($documento->funcionario_id !== $funcionario->id) {
            abort(404);
        }

        if (
            $documento->path &&
            Storage::disk('public')->exists($documento->path)
        ) {
            Storage::disk('public')->delete($documento->path);
        }

        $documento->delete();

        return back()->with('success', 'Documento removido com sucesso!');
    }

    // =============================
    // EXIBIR DETALHES
    // =============================
    public function show(Funcionario $funcionario)
    {
        $funcionario->load('empresa', 'documentos');

        return view('funcionarios.show', compact('funcionario'));
    }

    public function downloadDocumento(Funcionario $funcionario, DocumentoFuncionario $documento)
    {
        abort_unless((int) $documento->funcionario_id === (int) $funcionario->id, 404);
        abort_unless(Storage::disk('public')->exists($documento->path), 404);
        return Storage::disk('public')->download($documento->path, $documento->nome_original);
    }
}
