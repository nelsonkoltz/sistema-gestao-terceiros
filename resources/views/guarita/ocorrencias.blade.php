@extends('layouts.app')
@section('title', 'Ocorrências da portaria')
@push('styles')<link rel="stylesheet" href="{{ asset('css/gate-history.css') }}?v={{ filemtime(public_path('css/gate-history.css')) }}">@endpush
@section('content')
<div class="history-page">
    <header class="history-header"><div><span class="eyebrow">Portaria</span><h1>Ocorrências e bloqueios</h1><p>Rastreie situações operacionais mesmo quando nenhuma entrada foi liberada.</p></div><div class="history-actions"><a class="primary-action" href="{{ route('guarita.ocorrencias.exportar', request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV</a></div></header>
    @if($errors->any())<div class="history-alert">{{ $errors->first() }}</div>@endif
    <section class="filter-card"><form method="GET" action="{{ route('guarita.ocorrencias') }}">
        <div class="filter-field search-field"><label for="busca">Funcionário ou CPF</label><input id="busca" name="busca" value="{{ request('busca') }}" placeholder="Nome ou CPF"></div>
        <div class="filter-field"><label for="empresa_id">Empresa</label><select id="empresa_id" name="empresa_id"><option value="">Todas</option>@foreach($empresas as $empresa)<option value="{{ $empresa->id }}" {{ (string)request('empresa_id')===(string)$empresa->id?'selected':'' }}>{{ $empresa->nome }}</option>@endforeach</select></div>
        <div class="filter-field"><label for="tipo">Tipo</label><select id="tipo" name="tipo"><option value="">Todos</option><option value="bloqueio" {{ request('tipo')==='bloqueio'?'selected':'' }}>Bloqueio</option><option value="ocorrencia" {{ request('tipo')==='ocorrencia'?'selected':'' }}>Ocorrência</option></select></div>
        <div class="filter-field"><label for="categoria">Categoria</label><select id="categoria" name="categoria"><option value="">Todas</option>@foreach(['Documento fisico','Comportamento','Veiculo','Material','Seguranca','Outro'] as $categoria)<option {{ request('categoria')===$categoria?'selected':'' }}>{{ $categoria }}</option>@endforeach</select></div>
        <div class="filter-field"><label for="data_inicio">Data inicial</label><input id="data_inicio" type="date" name="data_inicio" value="{{ request('data_inicio') }}"></div>
        <div class="filter-field"><label for="data_fim">Data final</label><input id="data_fim" type="date" name="data_fim" value="{{ request('data_fim') }}"></div>
        <div class="filter-buttons"><button type="submit"><i class="bi bi-search"></i> Filtrar</button><a href="{{ route('guarita.ocorrencias') }}">Limpar</a></div>
    </form></section>
    <section class="history-card"><div class="table-heading"><div><h2>Registros encontrados</h2><p>{{ $registros->total() }} registro(s)</p></div></div><div class="history-table-wrap"><table><thead><tr><th>Pessoa</th><th>Empresa e serviço</th><th>Tipo</th><th>Descrição</th><th>Operador / origem</th></tr></thead><tbody>
    @forelse($registros as $registro)<tr><td><strong>{{ optional($registro->funcionario)->nome ?? 'Funcionário removido' }}</strong><small>{{ optional($registro->funcionario)->cpf_formatado }}</small></td><td><strong>{{ optional(optional($registro->funcionario)->empresa)->nome }}</strong><small>{{ optional($registro->servico)->descricao ?? 'Sem serviço vinculado' }}</small></td><td><span class="history-status {{ $registro->decisao === 'Bloqueado' ? 'bloqueado' : 'presente' }}">{{ $registro->decisao === 'Bloqueado' ? 'Bloqueio' : 'Ocorrência' }}</span><small>{{ $registro->categoria ?: 'Sem categoria' }}</small></td><td>{{ $registro->observacao ?: $registro->motivo }}</td><td>{{ optional($registro->operador)->name }}<small>{{ $registro->created_at->format('d/m/Y H:i:s') }} · IP {{ $registro->endereco_ip ?: 'não informado' }}</small></td></tr>
    @empty<tr><td colspan="5"><div class="history-empty"><i class="bi bi-shield-check"></i><strong>Nenhum registro encontrado</strong><span>Altere os filtros ou registre uma ocorrência na consulta da portaria.</span></div></td></tr>@endforelse
    </tbody></table></div><div class="history-pagination">{{ $registros->links() }}</div></section>
</div>
@endsection
