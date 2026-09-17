<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\Empresa;
use App\Models\Setor;
use App\Http\Requests\ServicoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * =============================
     * LISTAGEM DE SERVIÇOS
     * =============================
     */
    public function index(Request $request)
    {
        $query = Servico::with(['empresa', 'solicitante', 'setor']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                  ->orWhereHas('empresa', fn ($q) =>
                        $q->where('nome', 'like', "%{$search}%")
                    )
                  ->orWhereHas('setor', fn ($q) =>
                        $q->where('nome', 'like', "%{$search}%")
                    );
            });
        }

        $servicos = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // coleções auxiliares
        $empresas = Empresa::orderBy('nome')->get();
        $setores  = Setor::orderBy('nome')->get();

        return view('servicos.index', compact(
            'servicos',
            'empresas',
            'setores'
        ));
    }

    /**
     * =============================
     * FORMULÁRIO DE CRIAÇÃO
     * =============================
     */
    public function create()
    {
        $empresas = Empresa::orderBy('nome')->get();
        $setores  = Setor::orderBy('nome')->get();

        return view('servicos.create', compact('empresas', 'setores'));
    }

    /**
     * =============================
     * SALVAR NOVO SERVIÇO
     * =============================
     */
    public function store(ServicoRequest $request)
    {
        $data = $request->validated();
        $data['solicitante_id'] = Auth::id();
        $data['status'] = 'Pendente';
        $data['data_conclusao'] = null;

        Servico::create($data);

        return redirect()
            ->route('servicos.index')
            ->with('success', 'Serviço cadastrado com sucesso!');
    }

    /**
     * =============================
     * DETALHES DO SERVIÇO
     * =============================
     */
    public function show($id)
    {
        $servico = Servico::with([
            'empresa',
            'solicitante',
            'setor'
        ])->findOrFail($id);

        return view('servicos.show', compact('servico'));
    }

    /**
     * =============================
     * FORMULÁRIO DE EDIÇÃO
     * =============================
     */
    public function edit($id)
    {
        $servico = Servico::with([
            'empresa',
            'setor'
        ])->findOrFail($id);

        $empresas = Empresa::orderBy('nome')->get();
        $setores  = Setor::orderBy('nome')->get();

        return view('servicos.edit', compact(
            'servico',
            'empresas',
            'setores'
        ));
    }

    /**
     * =============================
     * ATUALIZAR SERVIÇO
     * =============================
     */
    public function update(ServicoRequest $request, $id)
    {
        $servico = Servico::findOrFail($id);

        $servico->update(
            $request->validated()
        );

        return redirect()
            ->route('servicos.index')
            ->with('success', 'Serviço atualizado com sucesso!');
    }

    /**
     * =============================
     * EXCLUIR SERVIÇO
     * =============================
     */
    public function destroy($id)
    {
        $servico = Servico::findOrFail($id);
        $servico->delete();

        return redirect()
            ->route('servicos.index')
            ->with('success', 'Serviço excluído com sucesso!');
    }
}
