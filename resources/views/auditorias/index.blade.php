@extends('layouts.app')
@section('title','Auditoria')
@push('styles')<link rel="stylesheet" href="{{ asset('css/audit.css') }}?v={{ filemtime(public_path('css/audit.css')) }}">@endpush
@section('content')
<div class="audit-page">
 <header class="audit-header"><div><span class="eyebrow">Administração</span><h1>Auditoria do sistema</h1><p>Histórico imutável das operações realizadas no TerceirosCR.</p></div><span class="audit-lock"><i class="bi bi-shield-lock"></i> Somente leitura</span></header>
 <section class="audit-filter"><form method="GET">
  <label>Busca<input name="busca" value="{{ request('busca') }}" placeholder="Usuário, ID ou IP"></label>
  <label>Usuário<select name="usuario_id"><option value="">Todos</option>@foreach($usuarios as $usuario)<option value="{{ $usuario->id }}" {{ (string)request('usuario_id')===(string)$usuario->id?'selected':'' }}>{{ $usuario->name }}</option>@endforeach</select></label>
  <label>Módulo<select name="modulo"><option value="">Todos</option>@foreach($modulos as $modulo)<option value="{{ $modulo }}" {{ request('modulo')===$modulo?'selected':'' }}>{{ $modulo }}</option>@endforeach</select></label>
  <label>Ação<select name="acao"><option value="">Todas</option>@foreach(['Criado','Alterado','Excluído'] as $acao)<option value="{{ $acao }}" {{ request('acao')===$acao?'selected':'' }}>{{ $acao }}</option>@endforeach</select></label>
  <label>De<input type="date" name="data_inicio" value="{{ request('data_inicio') }}"></label><label>Até<input type="date" name="data_fim" value="{{ request('data_fim') }}"></label>
  <div><button><i class="bi bi-search"></i> Filtrar</button><a href="{{ route('auditorias.index') }}">Limpar</a></div>
 </form></section>
 <section class="audit-card"><div class="audit-card-head"><h2>Eventos registrados</h2><span>{{ $auditorias->total() }} evento(s)</span></div><div class="audit-table"><table><thead><tr><th>Data</th><th>Usuário</th><th>Módulo</th><th>Ação</th><th>Registro</th><th>Origem</th><th></th></tr></thead><tbody>
 @forelse($auditorias as $auditoria)<tr><td><strong>{{ $auditoria->created_at->format('d/m/Y') }}</strong><small>{{ $auditoria->created_at->format('H:i:s') }}</small></td><td><strong>{{ optional($auditoria->usuario)->name ?? 'Sistema automático' }}</strong><small>{{ optional($auditoria->usuario)->username }}</small></td><td>{{ $auditoria->modulo }}</td><td><span class="audit-action {{ Str::slug($auditoria->acao) }}">{{ $auditoria->acao }}</span></td><td>#{{ $auditoria->registro_id ?? '—' }}</td><td>{{ $auditoria->ip ?? 'Processo interno' }}</td><td><a href="{{ route('auditorias.show',$auditoria) }}" title="Ver detalhes"><i class="bi bi-eye"></i></a></td></tr>
 @empty<tr><td colspan="7"><div class="audit-empty"><i class="bi bi-journal-x"></i><strong>Nenhum evento encontrado</strong></div></td></tr>@endforelse
 </tbody></table></div><div class="audit-pagination">{{ $auditorias->links() }}</div></section>
</div>
@endsection
