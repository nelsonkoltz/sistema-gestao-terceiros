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
            'setor',
            'canceladoPor'
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
        abort_if($servico->status === 'Cancelado', 422, 'Uma solicitacao cancelada nao pode ser editada.');

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
        abort_if($servico->status === 'Cancelado', 422, 'Uma solicitacao cancelada nao pode ser alterada.');
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
            return redirect()->route('servicos.index')
                ->with('error', 'Esta solicitação possui histórico e não pode ser excluída. Use a ação Cancelar e informe o motivo.');
        }

        $servico->delete();

        return redirect()
            ->route('servicos.index')
            ->with('success', 'Serviço excluído com sucesso!');
    }

    public function cancelar(Request $request, Servico $servico)
    {
        $this->garantirAcesso($servico);
        $dados = $request->validate([
            'motivo_cancelamento' => 'required|string|min:5|max:1000',
        ], [
            'motivo_cancelamento.required' => 'Informe o motivo do cancelamento.',
            'motivo_cancelamento.min' => 'O motivo deve ter pelo menos 5 caracteres.',
        ]);

        if ($servico->status === 'Cancelado') return back()->with('error', 'Esta solicitacao ja esta cancelada.');
        if ($servico->status === 'Finalizado') return back()->with('error', 'Uma solicitacao finalizada nao pode ser cancelada.');

        $servico->update([
            'status' => 'Cancelado',
            'motivo_cancelamento' => trim($dados['motivo_cancelamento']),
            'cancelado_por_id' => $request->user()->id,
            'cancelado_em' => now(),
        ]);

        return redirect()->route('servicos.show', $servico)
            ->with('success', 'Solicitacao cancelada. Novas entradas foram bloqueadas.');
    }

    private function garantirAcesso(Servico $servico): void
    {
        if (Auth::user()->permissao === 'Solicitante') {
            abort_unless((int) $servico->solicitante_id === (int) Auth::id(), 403);
        }
    }
}
