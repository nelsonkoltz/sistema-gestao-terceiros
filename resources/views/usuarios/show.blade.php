@extends('layouts.app')
@section('title','Detalhes do Usuário')
@push('styles')<link rel="stylesheet" href="{{ asset('css/details.css') }}?v={{ filemtime(public_path('css/details.css')) }}">@endpush
@section('content')
@php $permissionClass=$usuario->permissao==='Administrador'?'administrador':($usuario->permissao==='Consulta'?'consulta':'usuario'); @endphp
<div class="details-page">
 <nav class="breadcrumb"><a href="{{ route('usuarios.index') }}">Usuários</a><i class="bi bi-chevron-right"></i><span>Detalhes</span></nav>
 <header class="detail-header"><div class="identity"><span class="identity-icon"><i class="bi bi-person-gear"></i></span><div><span class="eyebrow">Usuário do sistema</span><h1>{{ $usuario->name }}</h1><p>{{ '@'.$usuario->username }}@if(auth()->id()===$usuario->id) · Você@endif</p></div></div><div class="header-actions"><a href="{{ route('usuarios.index') }}" class="secondary-btn"><i class="bi bi-arrow-left"></i> Voltar</a><a href="{{ route('usuarios.edit',$usuario) }}" class="primary-btn"><i class="bi bi-pencil"></i> Editar usuário</a></div></header>
 <section class="summary-grid"><div class="summary-card"><i class="bi bi-shield-check"></i><span><strong><span class="badge {{ $permissionClass }}">{{ $usuario->permissao }}</span></strong><small>Nível de permissão</small></span></div><div class="summary-card"><i class="bi bi-diagram-3"></i><span><strong>{{ $usuario->setor ?: '—' }}</strong><small>Setor</small></span></div><div class="summary-card"><i class="bi bi-calendar-check"></i><span><strong>{{ optional($usuario->created_at)->format('d/m/Y') }}</strong><small>Cadastro</small></span></div></section>
 <div class="content-grid single"><section class="panel"><div class="panel-header"><div><h2>Informações do usuário</h2><p>Identificação e acesso ao sistema.</p></div></div><dl class="info-grid"><div><dt>Nome completo</dt><dd>{{ $usuario->name }}</dd></div><div><dt>Login</dt><dd>{{ $usuario->username }}</dd></div><div><dt>Setor</dt><dd>{{ $usuario->setor ?: '—' }}</dd></div><div><dt>E-mail</dt><dd>@if($usuario->email)<a href="mailto:{{ $usuario->email }}">{{ $usuario->email }}</a>@else—@endif</dd></div><div><dt>Criado em</dt><dd>{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</dd></div><div><dt>Última atualização</dt><dd>{{ optional($usuario->updated_at)->format('d/m/Y H:i') }}</dd></div></dl></section></div>
</div>
@endsection
