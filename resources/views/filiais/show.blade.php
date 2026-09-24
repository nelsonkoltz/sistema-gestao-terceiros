@extends('layouts.app')
@section('title','Detalhes da filial')
@push('styles')<link rel="stylesheet" href="{{ asset('css/branches.css') }}?v={{ filemtime(public_path('css/branches.css')) }}">@endpush
@section('content')
<div class="branch-form-page">
 <nav class="branch-breadcrumb"><a href="{{ route('filiais.index') }}">Filiais</a><i class="bi bi-chevron-right"></i><span>Detalhes</span></nav>
 @if(session('success'))<div class="branch-alert success">{{ session('success') }}</div>@endif
 @if(session('error'))<div class="branch-alert danger">{{ session('error') }}</div>@endif
 <section class="branch-detail-head"><div><span class="branch-status {{ $filial->ativa?'active':'inactive' }}">{{ $filial->ativa?'Ativa':'Inativa' }}</span><h1>{{ $filial->nome }}</h1><p>{{ $filial->codigo }}</p></div><div><a href="{{ route('filiais.index') }}" class="secondary">Voltar</a><a href="{{ route('filiais.edit',$filial) }}" class="primary"><i class="bi bi-pencil"></i> Editar</a></div></section>
 <section class="branch-form-card branch-details"><h2>Dados da unidade</h2><dl><div><dt>CNPJ</dt><dd>{{ $filial->cnpj?:'—' }}</dd></div><div><dt>Telefone</dt><dd>{{ $filial->telefone?:'—' }}</dd></div><div><dt>E-mail</dt><dd>{{ $filial->email?:'—' }}</dd></div><div><dt>Localização</dt><dd>{{ collect([$filial->endereco,$filial->cidade,$filial->estado])->filter()->implode(' · ') ?: '—' }}</dd></div></dl></section>
 <section class="branch-form-card branch-users"><h2>Usuários vinculados <span>{{ $filial->usuarios->count() }}</span></h2>@forelse($filial->usuarios as $usuario)<div><span class="mini-avatar">{{ mb_strtoupper(mb_substr($usuario->name,0,1)) }}</span><span><strong>{{ $usuario->name }}</strong><small>{{ $usuario->permissao }}{{ $usuario->pivot->principal ? ' · Filial principal' : '' }}</small></span></div>@empty<p>Nenhum usuário vinculado.</p>@endforelse</section>
</div>
@endsection
