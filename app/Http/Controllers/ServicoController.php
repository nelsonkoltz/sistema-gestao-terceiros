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

        if (Auth::user()->permissao === 'Solicitante') {
            $query->where('solicitante_id', Auth::id());
        }

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
        $empresas = Empresa::where('ativo', true)->orderBy('nome')->get();
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
        $empresas = Empresa::where('ativo', true)->orderBy('nome')->get();
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
        $data['status'] = 'Agendado';
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
            'empresa.documentos',
            'empresa.funcionarios.documentos',
            'solicitante',
            'setor'
        ])->findOrFail($id);
        $this->garantirAcesso($servico);

        $funcionariosElegiveis = $servico->empresa
            ? $servico->empresa->funcionarios->filter(fn ($funcionario) => $funcionario->documentacaoRegular())
            : collect();

        return view('servicos.show', compact('servico', 'funcionariosElegiveis'));
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
        $this->garantirAcesso($servico);

        $empresas = Empresa::where('ativo', true)
            ->orWhere('id', $servico->empresa_id)
            ->orderBy('nome')->get();
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
        $this->garantirAcesso($servico);
        $data = $request->validated();

        if (Auth::user()->permissao === 'Solicitante') {
            $data['status'] = $servico->status;
        }

        $servico->update($data);

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
        $this->garantirAcesso($servico);

        if ($servico->registrosAcesso()->exists()) {
            if (!in_array($servico->status, ['Finalizado', 'Cancelado'], true)) {
                $servico->update(['status' => 'Cancelado']);
            }

            return redirect()->route('servicos.index')
                ->with('success', 'O serviço possui histórico de acesso e foi preservado' . ($servico->status === 'Cancelado' ? ' como cancelado.' : '.'));
        }

        $servico->delete();

        return redirect()
            ->route('servicos.index')
            ->with('success', 'Serviço excluído com sucesso!');
    }

    private function garantirAcesso(Servico $servico): void
    {
        if (Auth::user()->permissao === 'Solicitante') {
            abort_unless((int) $servico->solicitante_id === (int) Auth::id(), 403);
        }
    }
}
