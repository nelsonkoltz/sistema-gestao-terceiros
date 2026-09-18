@extends('layouts.app')
@section('title', 'Histórico da portaria')
@push('styles')<link rel="stylesheet" href="{{ asset('css/gate-history.css') }}?v={{ filemtime(public_path('css/gate-history.css')) }}">@endpush
@section('content')
<div class="history-page">
    <header class="history-header">
        <div><span class="eyebrow">Portaria</span><h1>Histórico de acessos</h1><p>Consulte entradas, saídas e tentativas de acesso bloqueadas.</p></div>
        <div class="history-actions">
            <button type="button" class="secondary-action" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
            <a class="primary-action" href="{{ route('guarita.historico.exportar', request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV</a>
        </div>
    </header>

    @if($errors->any())<div class="history-alert">{{ $errors->first() }}</div>@endif

    <section class="filter-card">
        <form method="GET" action="{{ route('guarita.historico') }}">
            <div class="filter-field search-field"><label for="busca">Funcionário ou CPF</label><input id="busca" name="busca" value="{{ request('busca') }}" placeholder="Nome ou CPF"></div>
            <div class="filter-field"><label for="empresa_id">Empresa</label><select id="empresa_id" name="empresa_id"><option value="">Todas</option>@foreach($empresas as $empresa)<option value="{{ $empresa->id }}" {{ (string)request('empresa_id')===(string)$empresa->id?'selected':'' }}>{{ $empresa->nome }}</option>@endforeach</select></div>
            <div class="filter-field"><label for="situacao">Situação</label><select id="situacao" name="situacao"><option value="">Todas</option><option value="presente" {{ request('situacao')==='presente'?'selected':'' }}>Presente</option><option value="finalizado" {{ request('situacao')==='finalizado'?'selected':'' }}>Finalizado</option><option value="bloqueado" {{ request('situacao')==='bloqueado'?'selected':'' }}>Bloqueado</option></select></div>
            <div class="filter-field"><label for="data_inicio">Data inicial</label><input id="data_inicio" type="date" name="data_inicio" value="{{ request('data_inicio') }}"></div>
            <div class="filter-field"><label for="data_fim">Data final</label><input id="data_fim" type="date" name="data_fim" value="{{ request('data_fim') }}"></div>
            <div class="filter-buttons"><button type="submit"><i class="bi bi-search"></i> Filtrar</button><a href="{{ route('guarita.historico') }}">Limpar</a></div>
        </form>
    </section>

    <section class="history-card">
        <div class="table-heading"><div><h2>Registros encontrados</h2><p>{{ $registros->total() }} ocorrência(s)</p></div></div>
        <div class="history-table-wrap">
            <table>
                <thead><tr><th>Funcionário</th><th>Empresa e serviço</th><th>Entrada</th><th>Saída</th><th>Situação</th><th>Operador</th></tr></thead>
                <tbody>
                @forelse($registros as $registro)
                    @php
                        $situacao = $registro->decisao === 'Bloqueado' ? 'Bloqueado' : ($registro->saida_em ? 'Finalizado' : 'Presente');
                        $classe = mb_strtolower($situacao);
                    @endphp
                    <tr>
                        <td><strong>{{ optional($registro->funcionario)->nome ?? 'Funcionário removido' }}</strong><small>{{ optional($registro->funcionario)->cpf_formatado ?? 'CPF não disponível' }}</small></td>
                        <td><strong>{{ optional(optional($registro->funcionario)->empresa)->nome ?? 'Empresa não disponível' }}</strong><small>{{ optional($registro->servico)->descricao ?? 'Sem serviço vinculado' }}</small>@if(optional(optional($registro->servico)->setor)->nome)<small>Destino: {{ $registro->servico->setor->nome }}</small>@endif</td>
                        <td>{{ $registro->entrada_em ? $registro->entrada_em->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $registro->saida_em ? $registro->saida_em->format('d/m/Y H:i') : '—' }}</td>
                        <td><span class="history-status {{ $classe }}">{{ $situacao }}</span>@if($registro->motivo)<small class="block-reason">{{ $registro->motivo }}</small>@endif</td>
                        <td>{{ optional($registro->operador)->name ?? 'Não informado' }}<small>{{ $registro->created_at->format('d/m/Y H:i') }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="history-empty"><i class="bi bi-clock-history"></i><strong>Nenhum registro encontrado</strong><span>Altere os filtros ou registre um acesso na portaria.</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="history-pagination">{{ $registros->links() }}</div>
    </section>
</div>
@endsection
