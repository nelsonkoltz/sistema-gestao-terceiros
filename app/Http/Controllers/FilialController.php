<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilialController extends Controller
{
    public function index(Request $request)
    {
        $filiais = Filial::withCount('usuarios')
            ->when($request->filled('busca'), function ($query) use ($request) {
                $busca = trim((string) $request->busca);
                $query->where(fn ($q) => $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('codigo', 'like', "%{$busca}%")
                    ->orWhere('cidade', 'like', "%{$busca}%"));
            })->orderBy('nome')->paginate(15)->withQueryString();
        return view('filiais.index', compact('filiais'));
    }

    public function create()
    {
        return view('filiais.form', ['filial' => new Filial]);
    }

    public function store(Request $request)
    {
        Filial::create($this->validar($request));
        return redirect()->route('filiais.index')->with('success', 'Filial cadastrada com sucesso.');
    }

    public function show(Filial $filial)
    {
        $filial->load(['usuarios' => fn ($q) => $q->orderBy('name')]);
        return view('filiais.show', compact('filial'));
    }

    public function edit(Filial $filial)
    {
        return view('filiais.form', compact('filial'));
    }

    public function update(Request $request, Filial $filial)
    {
        $filial->update($this->validar($request, $filial));
        return redirect()->route('filiais.show', $filial)->with('success', 'Filial atualizada com sucesso.');
    }

    public function destroy(Filial $filial)
    {
        if ($filial->usuarios()->exists()) {
            return back()->with('error', 'A filial possui usuários vinculados. Inative-a em vez de excluir.');
        }
        $filial->delete();
        return redirect()->route('filiais.index')->with('success', 'Filial excluída.');
    }

    private function validar(Request $request, ?Filial $filial = null): array
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('filiais', 'codigo')->ignore(optional($filial)->id)],
            'cnpj' => ['nullable', 'digits:14', Rule::unique('filiais', 'cnpj')->ignore(optional($filial)->id)],
            'telefone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|size:2',
            'ativa' => 'required|boolean',
        ]);
        $dados['codigo'] = mb_strtoupper($dados['codigo']);
        $dados['estado'] = $dados['estado'] ? mb_strtoupper($dados['estado']) : null;
        $dados['ativa'] = $request->boolean('ativa');
        return $dados;
    }
}
