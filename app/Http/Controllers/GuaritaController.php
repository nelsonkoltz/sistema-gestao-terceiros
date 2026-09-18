<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Empresa;
use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use App\Models\RegistroAcesso;
use App\Models\Servico;
use Illuminate\Http\Request;

class GuaritaController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->query('q'));
        $resultados = collect();

        if ($busca !== '') {
            $digitos = preg_replace('/\D/', '', $busca);
            $resultados = Funcionario::with(['empresa.documentos', 'documentos'])
                ->where(function ($query) use ($busca, $digitos) {
                    $query->where('nome', 'like', "%{$busca}%");
                    if ($digitos !== '') $query->orWhere('cpf', 'like', "%{$digitos}%");
                })->orderBy('nome')->limit(20)->get()
                ->map(fn ($funcionario) => $this->avaliar($funcionario));
        }

        $presentes = RegistroAcesso::with([
            'funcionario.empresa',
            'servico.empresa',
            'servico.setor',
            'servico.solicitante',
        ])
            ->whereNotNull('entrada_em')->whereNull('saida_em')->latest('entrada_em')->get();

        $inicioHoje = today();
        $fimHoje = today()->endOfDay();
        $indicadores = [
            'presentes' => $presentes->count(),
            'entradas' => RegistroAcesso::whereBetween('entrada_em', [$inicioHoje, $fimHoje])->count(),
            'saidas' => RegistroAcesso::whereBetween('saida_em', [$inicioHoje, $fimHoje])->count(),
            'bloqueios' => RegistroAcesso::where('decisao', 'Bloqueado')->whereBetween('created_at', [$inicioHoje, $fimHoje])->count(),
        ];

        $statusServicos = Servico::whereDate('data_servico', today())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $servicosHoje = Servico::with(['empresa', 'setor'])
            ->whereDate('data_servico', today())
            ->whereIn('status', ['Agendado', 'Em Andamento'])
            ->orderBy('hora_inicio')
            ->limit(8)
            ->get();

        $documentosVencendo = Documento::where('status', '!=', 'Substituido')
            ->whereBetween('validade_ate', [today(), today()->addDays(30)])
            ->count()
            + DocumentoFuncionario::where('status', '!=', 'Substituido')
                ->whereBetween('validade_ate', [today(), today()->addDays(30)])
                ->count();

        $entradasAtrasadas = $presentes->filter(function ($registro) {
            if ($registro->entrada_em->lt(today())) return true;
            return $registro->servico && $registro->servico->hora_fim
                && now()->format('H:i:s') > $registro->servico->hora_fim;
        })->count();

        return view('guarita.index', compact(
            'busca', 'resultados', 'presentes', 'indicadores', 'statusServicos',
            'servicosHoje', 'documentosVencendo', 'entradasAtrasadas'
        ));
    }

    public function historico(Request $request)
    {
        $this->validarFiltrosHistorico($request);
        $registros = $this->consultaHistorico($request)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();
        $empresas = Empresa::orderBy('nome')->get(['id', 'nome']);

        return view('guarita.historico', compact('registros', 'empresas'));
    }

    public function exportarHistorico(Request $request)
    {
        $this->validarFiltrosHistorico($request);
        $registros = $this->consultaHistorico($request)->latest('created_at')->get();
        $nome = 'historico-portaria-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($registros) {
            echo "\xEF\xBB\xBF";
            $arquivo = fopen('php://output', 'w');
            fputcsv($arquivo, ['Data', 'Funcionário', 'CPF', 'Empresa', 'Serviço', 'Setor', 'Entrada', 'Saída', 'Situação', 'Operador', 'Motivo'], ';');
            foreach ($registros as $registro) {
                fputcsv($arquivo, [
                    $registro->created_at->format('d/m/Y H:i'),
                    optional($registro->funcionario)->nome,
                    optional($registro->funcionario)->cpf,
                    optional(optional($registro->funcionario)->empresa)->nome,
                    optional($registro->servico)->descricao,
                    optional(optional($registro->servico)->setor)->nome,
                    optional($registro->entrada_em)->format('d/m/Y H:i'),
                    optional($registro->saida_em)->format('d/m/Y H:i'),
                    $this->situacaoRegistro($registro),
                    optional($registro->operador)->name,
                    $registro->motivo,
                ], ';');
            }
            fclose($arquivo);
        }, $nome, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function entrada(Request $request, Funcionario $funcionario)
    {
        $avaliacao = $this->avaliar($funcionario->load(['empresa.documentos', 'documentos']));
        if (!$avaliacao['liberado']) {
            RegistroAcesso::create([
                'funcionario_id' => $funcionario->id,
                'servico_id' => optional($avaliacao['servico'])->id,
                'registrado_por' => $request->user()->id,
                'decisao' => 'Bloqueado',
                'motivo' => implode(' ', $avaliacao['motivos']),
            ]);
            return back()->with('error', implode(' ', $avaliacao['motivos']));
        }

        if (RegistroAcesso::where('funcionario_id', $funcionario->id)->whereNull('saida_em')->whereNotNull('entrada_em')->exists()) {
            return back()->with('error', 'Este funcionário já possui uma entrada aberta.');
        }

        RegistroAcesso::create([
            'funcionario_id' => $funcionario->id,
            'servico_id' => $avaliacao['servico']->id,
            'registrado_por' => $request->user()->id,
            'decisao' => 'Liberado',
            'entrada_em' => now(),
        ]);
        $avaliacao['servico']->update([
            'status' => 'Em Andamento',
            'data_conclusao' => null,
        ]);
        return back()->with('success', 'Entrada registrada para ' . $funcionario->nome . '.');
    }

    public function saida(Request $request, RegistroAcesso $registro)
    {
        abort_if($registro->saida_em || !$registro->entrada_em, 422, 'Registro de acesso já encerrado.');
        $registro->update(['saida_em' => now()]);

        if ($registro->servico_id) {
            $temOutraEntradaAberta = RegistroAcesso::where('servico_id', $registro->servico_id)
                ->where('id', '!=', $registro->id)
                ->whereNotNull('entrada_em')
                ->whereNull('saida_em')
                ->exists();

            if (!$temOutraEntradaAberta) {
                $registro->servico()->update([
                    'status' => 'Finalizado',
                    'data_conclusao' => today()->toDateString(),
                ]);
            }
        }

        return back()->with('success', 'Saída registrada para ' . $registro->funcionario->nome . '.');
    }

    private function avaliar(Funcionario $funcionario): array
    {
        $servicosHoje = Servico::with(['empresa.documentos', 'setor', 'solicitante'])
            ->where('empresa_id', $funcionario->empresa_id)
            ->whereDate('data_servico', today())
            ->orderBy('hora_inicio')->get();

        $servicos = $servicosHoje->whereIn('status', ['Agendado', 'Em Andamento']);

        foreach ($servicos as $servico) {
            $motivos = $servico->motivosBloqueio($funcionario);
            if ($motivos === []) return compact('funcionario', 'servico') + ['liberado' => true, 'motivos' => []];
        }

        $motivos = [];
        if (!$funcionario->ativo) $motivos[] = 'Funcionário inativo.';
        if (!$funcionario->empresa->documentacaoRegular()) $motivos[] = 'Empresa com documentação irregular.';
        if (!$funcionario->documentacaoRegular()) $motivos[] = 'Funcionário com documentação irregular.';
        if ($servicosHoje->isEmpty()) $motivos[] = 'Nenhuma solicitação cadastrada para esta empresa hoje.';
        elseif ($servicos->isEmpty()) $motivos[] = 'A solicitação está finalizada ou cancelada.';
        elseif ($servicos->every(fn ($servico) => $servico->hora_inicio && now()->format('H:i:s') < $servico->hora_inicio)) $motivos[] = 'Entrada antes do horário autorizado.';
        else $motivos[] = 'Entrada fora do horário autorizado.';

        return [
            'funcionario' => $funcionario,
            'servico' => $servicos->first() ?? $servicosHoje->first(),
            'liberado' => false,
            'motivos' => array_values(array_unique($motivos)),
        ];
    }

    private function consultaHistorico(Request $request)
    {
        $query = RegistroAcesso::with([
            'funcionario.empresa',
            'servico.setor',
            'servico.solicitante',
            'operador',
        ]);

        if ($request->filled('busca')) {
            $busca = trim((string) $request->busca);
            $digitos = preg_replace('/\D/', '', $busca);
            $query->whereHas('funcionario', function ($q) use ($busca, $digitos) {
                $q->where('nome', 'like', "%{$busca}%");
                if ($digitos !== '') $q->orWhere('cpf', 'like', "%{$digitos}%");
            });
        }
        if ($request->filled('empresa_id')) {
            $query->whereHas('funcionario', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }
        if ($request->filled('data_inicio')) $query->whereDate('created_at', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->whereDate('created_at', '<=', $request->data_fim);

        if ($request->situacao === 'bloqueado') $query->where('decisao', 'Bloqueado');
        elseif ($request->situacao === 'presente') $query->whereNotNull('entrada_em')->whereNull('saida_em');
        elseif ($request->situacao === 'finalizado') $query->whereNotNull('saida_em');

        return $query;
    }

    private function validarFiltrosHistorico(Request $request): void
    {
        $request->validate([
            'busca' => 'nullable|string|max:100',
            'empresa_id' => 'nullable|integer|exists:empresas,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'situacao' => 'nullable|in:bloqueado,presente,finalizado',
        ]);
    }

    private function situacaoRegistro(RegistroAcesso $registro): string
    {
        if ($registro->decisao === 'Bloqueado') return 'Bloqueado';
        if ($registro->entrada_em && !$registro->saida_em) return 'Presente';
        if ($registro->saida_em) return 'Finalizado';
        return $registro->decisao;
    }
}
