@extends('layouts.app')
@section('title', 'Controle de acesso')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/guarita.css') }}?v={{ filemtime(public_path('css/guarita.css')) }}">
<link rel="stylesheet" href="{{ asset('css/guarita-servico.css') }}?v={{ filemtime(public_path('css/guarita-servico.css')) }}">
<link rel="stylesheet" href="{{ asset('css/gate-dashboard.css') }}?v={{ filemtime(public_path('css/gate-dashboard.css')) }}">
@endpush
@section('content')
<div class="gate-page">
    <header class="gate-header">
        <div><span class="eyebrow">Guarita</span><h1>Painel operacional</h1><p>Acompanhe a movimentação do dia e consulte o terceirizado.</p></div>
        <div class="header-tools"><span class="clock"><i class="bi bi-calendar3"></i>{{ now()->format('d/m/Y H:i') }}</span><a href="{{ route('guarita.historico') }}"><i class="bi bi-clock-history"></i> Histórico</a></div>
    </header>
    @if(session('success'))<div class="gate-alert success"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="gate-alert danger"><i class="bi bi-x-circle"></i>{{ session('error') }}</div>@endif

    <section class="gate-kpis">
        <article class="gate-kpi present"><span><i class="bi bi-people"></i></span><div><strong>{{ $indicadores['presentes'] }}</strong><small>Pessoas dentro</small></div></article>
        <article class="gate-kpi entry"><span><i class="bi bi-box-arrow-in-right"></i></span><div><strong>{{ $indicadores['entradas'] }}</strong><small>Entradas hoje</small></div></article>
        <article class="gate-kpi exit"><span><i class="bi bi-box-arrow-right"></i></span><div><strong>{{ $indicadores['saidas'] }}</strong><small>Saídas hoje</small></div></article>
        <article class="gate-kpi blocked"><span><i class="bi bi-shield-x"></i></span><div><strong>{{ $indicadores['bloqueios'] }}</strong><small>Bloqueios hoje</small></div></article>
    </section>

    @if($entradasAtrasadas || $documentosVencendo)
        <section class="operation-alerts">
            @if($entradasAtrasadas)<a href="#presentes"><i class="bi bi-exclamation-triangle"></i><strong>{{ $entradasAtrasadas }}</strong> entrada(s) aberta(s) além do período previsto</a>@endif
            @if($documentosVencendo)<div><i class="bi bi-file-earmark-excel"></i><strong>{{ $documentosVencendo }}</strong> documento(s) vencendo nos próximos 30 dias</div>@endif
        </section>
    @endif

    <section class="day-overview">
        <div class="day-services">
            <div class="section-title"><h2>Serviços de hoje</h2><span>{{ $servicosHoje->count() }} ativo(s)</span></div>
            <div class="service-timeline">
                @forelse($servicosHoje as $servico)
                    <article><time>{{ $servico->hora_inicio ? substr($servico->hora_inicio,0,5) : '—' }}</time><div><strong>{{ $servico->descricao }}</strong><small>{{ $servico->empresa->nome ?? 'Empresa não informada' }} · {{ $servico->setor->nome ?? 'Setor não informado' }}</small></div><span class="service-state {{ Str::slug($servico->status) }}">{{ $servico->status }}</span></article>
                @empty
                    <div class="day-empty"><i class="bi bi-calendar-check"></i>Nenhum serviço ativo para hoje.</div>
                @endforelse
            </div>
        </div>
        <aside class="day-status">
            <h2>Situação dos serviços</h2>
            <div><span>Agendados</span><strong>{{ $statusServicos['Agendado'] ?? 0 }}</strong></div>
            <div><span>Em andamento</span><strong>{{ $statusServicos['Em Andamento'] ?? 0 }}</strong></div>
            <div><span>Finalizados</span><strong>{{ $statusServicos['Finalizado'] ?? 0 }}</strong></div>
            <div><span>Cancelados</span><strong>{{ $statusServicos['Cancelado'] ?? 0 }}</strong></div>
        </aside>
    </section>
    <section class="search-card"><form method="GET" action="{{ route('guarita.index') }}"><label for="q">CPF ou nome do funcionário</label><div class="search-control"><i class="bi bi-search"></i><input id="q" name="q" value="{{ $busca }}" placeholder="Digite o CPF ou nome" autofocus autocomplete="off"><button type="submit">Consultar</button></div></form></section>

    @if($busca !== '')
        <section class="results-section">
            <div class="section-title"><h2>Resultado da consulta</h2><span>{{ $resultados->count() }} encontrado(s)</span></div>
            <div class="result-list">
                @forelse($resultados as $resultado)
                    @php($funcionario = $resultado['funcionario'])
                    @php($servico = $resultado['servico'])
                    <article class="access-card {{ $resultado['liberado'] ? 'allowed' : 'denied' }}">
                        <div class="decision"><i class="bi {{ $resultado['liberado'] ? 'bi-check-lg' : 'bi-x-lg' }}"></i><strong>{{ $resultado['liberado'] ? 'ENTRADA LIBERADA' : 'ENTRADA BLOQUEADA' }}</strong></div>
                        <div class="person"><span class="person-icon"><i class="bi bi-person"></i></span><div><h3>{{ $funcionario->nome }}</h3><p>{{ $funcionario->cpf_formatado }} · {{ $funcionario->empresa->nome }}</p></div></div>
                        <div class="access-details">
                            @if($servico)
                                <div class="service-summary">
                                    <strong><i class="bi bi-tools"></i>{{ $servico->descricao }}</strong>
                                    <span><i class="bi bi-building"></i>{{ optional($servico->setor)->nome ?? 'Setor não informado' }}</span>
                                    <span><i class="bi bi-person-badge"></i>Solicitante: {{ optional($servico->solicitante)->name ?? 'Não informado' }}</span>
                                    <span><i class="bi bi-clipboard-check"></i>Solicitação #{{ $servico->id }}</span>
                                    <span><i class="bi bi-activity"></i>Status: <b>{{ $servico->status }}</b></span>
                                    <span><i class="bi bi-clock"></i>@if($servico->hora_inicio && $servico->hora_fim){{ substr($servico->hora_inicio, 0, 5) }} às {{ substr($servico->hora_fim, 0, 5) }}@else Horário não informado @endif</span>
                                </div>
                            @endif
                            @if(!$resultado['liberado'])<ul>@foreach($resultado['motivos'] as $motivo)<li>{{ $motivo }}</li>@endforeach</ul>@endif
                        </div>
                        @if($resultado['ocorrencias']->isNotEmpty())
                            <div class="recent-incidents"><strong><i class="bi bi-exclamation-triangle"></i> Registros anteriores</strong>@foreach($resultado['ocorrencias'] as $item)<small>{{ $item->created_at->format('d/m/Y H:i') }} · {{ $item->decisao === 'Bloqueado' ? 'Bloqueio' : ($item->categoria ?: 'Ocorrência') }} · {{ $item->observacao ?: $item->motivo }}</small>@endforeach</div>
                        @endif
                        <div class="gate-record-actions">
                            @if($resultado['liberado'])<details><summary><i class="bi bi-box-arrow-in-right"></i> Registrar entrada</summary><form method="POST" action="{{ route('guarita.entrada', $funcionario) }}">@csrf<textarea name="observacao" maxlength="1000" placeholder="Observação da entrada (opcional)"></textarea><button class="entry-button" type="submit">Confirmar entrada</button></form></details>@endif
                            <details class="incident-form"><summary>Registrar ocorrência</summary><form method="POST" action="{{ route('guarita.ocorrencias.registrar', $funcionario) }}">@csrf @if($servico)<input type="hidden" name="servico_id" value="{{ $servico->id }}">@endif<select name="categoria" required aria-label="Categoria da ocorrência"><option value="">Selecione a categoria</option><option>Documento fisico</option><option>Comportamento</option><option>Veiculo</option><option>Material</option><option>Seguranca</option><option>Outro</option></select><textarea name="observacao" required maxlength="1000" placeholder="Descreva a ocorrência"></textarea><button type="submit">Salvar ocorrência sem liberar entrada</button></form></details>
                        </div>
                    </article>
                @empty
                    <div class="empty-result"><i class="bi bi-person-x"></i><strong>Nenhum funcionário encontrado</strong><span>Confira o CPF ou nome informado.</span></div>
                @endforelse
            </div>
        </section>
    @endif

    <section class="present-section" id="presentes">
        <div class="section-title"><h2>Pessoas dentro da empresa</h2><span class="present-count">{{ $presentes->count() }}</span></div>
        <div class="present-list">
            @forelse($presentes as $registro)
                <div class="present-row"><span class="presence-dot"></span><div><strong>{{ $registro->funcionario->nome }}</strong><small>{{ $registro->funcionario->empresa->nome }} · entrada às {{ $registro->entrada_em->format('H:i') }}</small><small class="present-service"><i class="bi bi-tools"></i> {{ optional($registro->servico)->descricao ?? 'Serviço não informado' }}@if(optional(optional($registro->servico)->setor)->nome) · {{ $registro->servico->setor->nome }}@endif</small></div><details class="exit-form"><summary>Registrar saída</summary><form method="POST" action="{{ route('guarita.saida', $registro) }}">@csrf @method('PUT')<textarea name="observacao_saida" maxlength="1000" placeholder="Observação da saída (opcional)"></textarea><button type="submit">Confirmar saída</button></form></details></div>
            @empty
                <div class="empty-present">Nenhum terceirizado está com entrada aberta.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
