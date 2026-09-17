@extends('layouts.app')

@section('title', 'Início')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}">
@endpush

@section('content')
@php
    $hora = (int) now()->format('H');
    $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
@endphp

<div class="dashboard">
    <header class="dashboard-header">
        <div>
            <span class="eyebrow">Visão geral</span>
            <h1>{{ $saudacao }}, {{ auth()->user()->name }}</h1>
            <p>Acompanhe as solicitações e acesse rapidamente as rotinas do TerceirosCR.</p>
        </div>
        @if(auth()->user()->permissao !== 'Consulta')
            <a href="{{ route('servicos.create') }}" class="primary-action"><i class="bi bi-plus-circle"></i> Nova solicitação</a>
        @endif
    </header>

    <section class="metric-grid" aria-label="Indicadores">
        <a href="{{ route('servicos.index') }}" class="metric-card warning">
            <span class="metric-icon"><i class="bi bi-clock-history"></i></span>
            <span><strong>{{ $counts['pendentes'] }}</strong><small>Solicitações pendentes</small></span>
        </a>
        <a href="{{ route('servicos.index') }}" class="metric-card info">
            <span class="metric-icon"><i class="bi bi-calendar-check"></i></span>
            <span><strong>{{ $counts['hoje'] }}</strong><small>Serviços previstos hoje</small></span>
        </a>
        <a href="{{ route('funcionarios.index') }}" class="metric-card success">
            <span class="metric-icon"><i class="bi bi-people"></i></span>
            <span><strong>{{ $counts['funcionarios'] }}</strong><small>Terceirizados ativos</small></span>
        </a>
        <a href="{{ route('empresas.index') }}" class="metric-card neutral">
            <span class="metric-icon"><i class="bi bi-buildings"></i></span>
            <span><strong>{{ $counts['empresas'] }}</strong><small>Empresas cadastradas</small></span>
        </a>
    </section>

    <div class="dashboard-grid">
        <section class="panel upcoming-panel">
            <div class="panel-header">
                <div><h2>Próximos serviços</h2><p>Solicitações pendentes ou em andamento.</p></div>
                <a href="{{ route('servicos.index') }}">Ver todos <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="upcoming-list">
                @forelse($proximosServicos as $servico)
                    <a href="{{ route('servicos.show', $servico) }}" class="upcoming-item">
                        <time datetime="{{ $servico->data_servico->format('Y-m-d') }}">
                            <strong>{{ $servico->data_servico->format('d') }}</strong>
                            <span>{{ mb_strtoupper($servico->data_servico->locale('pt_BR')->translatedFormat('M')) }}</span>
                        </time>
                        <span class="upcoming-content">
                            <strong>{{ $servico->empresa->nome ?? 'Empresa não informada' }}</strong>
                            <small>{{ $servico->setor->nome ?? 'Sem setor' }} · {{ $servico->descricao }}</small>
                        </span>
                        <span class="status-badge status-{{ str_replace(' ', '-', mb_strtolower($servico->status)) }}">{{ $servico->status }}</span>
                    </a>
                @empty
                    <div class="empty-state compact"><i class="bi bi-calendar2-check"></i><strong>Nenhum serviço próximo</strong><span>As novas solicitações aparecerão aqui.</span></div>
                @endforelse
            </div>
        </section>

        <aside class="panel shortcuts-panel">
            <div class="panel-header"><div><h2>Acessos rápidos</h2><p>Rotinas mais utilizadas.</p></div></div>
            <nav class="shortcut-list">
                <a href="{{ route('servicos.index') }}"><i class="bi bi-clipboard2-check"></i><span><strong>Solicitações</strong><small>Consultar serviços</small></span><i class="bi bi-chevron-right"></i></a>
                <a href="{{ route('funcionarios.index') }}"><i class="bi bi-person-badge"></i><span><strong>Terceirizados</strong><small>Consultar pessoas e documentos</small></span><i class="bi bi-chevron-right"></i></a>
                <a href="{{ route('empresas.index') }}"><i class="bi bi-building"></i><span><strong>Empresas</strong><small>Consultar prestadores</small></span><i class="bi bi-chevron-right"></i></a>
            </nav>
        </aside>
    </div>
</div>
@endsection
