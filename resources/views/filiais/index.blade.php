@extends('layouts.app')
@section('title','Filiais')
@push('styles')<link rel="stylesheet" href="{{ asset('css/branches.css') }}?v={{ filemtime(public_path('css/branches.css')) }}">@endpush
@section('content')
<div class="branches-page">
 <header class="branches-header"><div><span class="eyebrow">Administração</span><h1>Filiais</h1><p>Gerencie as unidades que utilizarão o TerceirosCR.</p></div><a href="{{ route('filiais.create') }}" class="primary-action"><i class="bi bi-plus-circle"></i> Nova filial</a></header>
 @if(session('success'))<div class="branch-alert success">{{ session('success') }}</div>@endif
 @if(session('error'))<div class="branch-alert danger">{{ session('error') }}</div>@endif
 <section class="branches-card">
  <div class="branches-toolbar"><form method="GET"><i class="bi bi-search"></i><input name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome, código ou cidade"><button>Buscar</button></form><span>{{ $filiais->total() }} unidade(s)</span></div>
  <div class="branches-grid">
   @forelse($filiais as $filial)
   <article class="branch-item"><div class="branch-top"><span class="branch-icon"><i class="bi bi-building"></i></span><span class="branch-status {{ $filial->ativa?'active':'inactive' }}">{{ $filial->ativa?'Ativa':'Inativa' }}</span></div><h2><a href="{{ route('filiais.show',$filial) }}">{{ $filial->nome }}</a></h2><p>{{ $filial->codigo }} · {{ $filial->cidade ? $filial->cidade.'/'.($filial->estado ?: '—') : 'Localização não informada' }}</p><div class="branch-meta"><span><i class="bi bi-people"></i>{{ $filial->usuarios_count }} usuário(s)</span></div><div class="branch-actions"><a href="{{ route('filiais.show',$filial) }}">Detalhes</a><a href="{{ route('filiais.edit',$filial) }}"><i class="bi bi-pencil"></i> Editar</a></div></article>
   @empty<div class="branches-empty"><i class="bi bi-buildings"></i><strong>Nenhuma filial encontrada</strong><p>Cadastre a primeira unidade operacional.</p></div>@endforelse
  </div>
  <div class="pagination">{{ $filiais->links() }}</div>
 </section>
</div>
@endsection
