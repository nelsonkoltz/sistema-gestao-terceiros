@extends('layouts.app')
@section('title', 'Detalhes da Empresa')
@push('styles')<link rel="stylesheet" href="{{ asset('css/empresas/show.css') }}?v={{ filemtime(public_path('css/empresas/show.css')) }}">@endpush

@section('content')
<div class="details-page">
    <nav class="breadcrumb" aria-label="Navegação"><a href="{{ route('empresas.index') }}">Empresas</a><i class="bi bi-chevron-right"></i><span>Detalhes</span></nav>

    <header class="company-header">
        <div class="company-identity"><span class="company-icon"><i class="bi bi-building"></i></span><div><span class="eyebrow">{{ $empresa->tipo === 'CPF' ? 'Prestador pessoa física' : 'Empresa terceirizada' }}</span><h1>{{ $empresa->nome }}</h1><p>Cadastrada em {{ optional($empresa->created_at)->format('d/m/Y') }}</p></div></div>
        <div class="header-actions"><a href="{{ route('empresas.index') }}" class="secondary-btn"><i class="bi bi-arrow-left"></i> Voltar</a>@if(auth()->user()->permissao !== 'Consulta')<a href="{{ route('empresas.edit',$empresa) }}" class="primary-btn"><i class="bi bi-pencil"></i> Editar empresa</a>@endif</div>
    </header>

    <section class="summary-grid">
        <div class="summary-card"><i class="bi bi-people"></i><span><strong>{{ $empresa->funcionarios_count }}</strong><small>Funcionários cadastrados</small></span></div>
        <div class="summary-card"><i class="bi bi-person-check"></i><span><strong>{{ $empresa->funcionarios_ativos_count }}</strong><small>Funcionários ativos</small></span></div>
        <div class="summary-card"><i class="bi bi-files"></i><span><strong>{{ $empresa->documentos->count() }}</strong><small>Documentos anexados</small></span></div>
    </section>

    <div class="content-grid">
        <section class="panel info-panel">
            <div class="panel-header"><div><h2>Informações cadastrais</h2><p>Dados de identificação e contato.</p></div></div>
            <dl class="info-grid">
                <div><dt>{{ $empresa->tipo === 'CPF' ? 'CPF' : 'CNPJ' }}</dt><dd>{{ $empresa->cnpj_formatado }}</dd></div>
                <div><dt>Telefone</dt><dd><a href="tel:{{ preg_replace('/\D/','',$empresa->telefone) }}">{{ $empresa->telefone_formatado }}</a></dd></div>
                <div class="full"><dt>E-mail</dt><dd><a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a></dd></div>
            </dl>
            <div class="address-block"><span class="address-icon"><i class="bi bi-geo-alt"></i></span><div><strong>Endereço</strong><address>{{ $empresa->endereco_rua }}, {{ $empresa->endereco_numero }}<br>{{ $empresa->endereco_bairro }} · {{ $empresa->endereco_cidade }}/{{ $empresa->endereco_estado }}<br>CEP {{ $empresa->cep_formatado }}</address></div></div>
        </section>

        <section class="panel documents-panel">
            <div class="panel-header"><div><h2>Documentos</h2><p>{{ $empresa->documentos->count() }} {{ $empresa->documentos->count() === 1 ? 'arquivo anexado' : 'arquivos anexados' }}</p></div>@if(auth()->user()->permissao !== 'Consulta')<a href="{{ route('empresas.edit',$empresa) }}#documentos" class="panel-action"><i class="bi bi-plus"></i> Adicionar</a>@endif</div>
            <div class="document-list">
                @forelse($empresa->documentos as $documento)
                    <div class="document-item"><span class="document-icon"><i class="bi bi-file-earmark-pdf"></i></span><span class="document-info"><strong title="{{ $documento->nome_arquivo }}">{{ $documento->nome_arquivo }}</strong><small>Adicionado em {{ $documento->created_at->format('d/m/Y \à\s H:i') }}</small></span><a href="{{ route('empresas.documentos.download',[$empresa,$documento]) }}" class="download-btn" title="Baixar {{ $documento->nome_arquivo }}"><i class="bi bi-download"></i><span>Baixar</span></a></div>
                @empty
                    <div class="empty-documents"><i class="bi bi-file-earmark"></i><strong>Nenhum documento anexado</strong><span>Os documentos desta empresa aparecerão aqui.</span></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
