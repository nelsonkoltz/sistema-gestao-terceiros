@extends('layouts.app')

@section('title', 'Detalhes do Funcionário')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/funcionarios/show.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Detalhes do Funcionário</h1>
        <p class="form-subtitle">
            Visualização completa das informações cadastradas.
        </p>
    </header>

    {{-- DADOS --}}
    <div class="details-grid">

        <div class="detail-item">
            <span class="detail-label">Nome</span>
            <span class="detail-value">{{ $funcionario->nome }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">CPF</span>
            <span class="detail-value">
                {{ method_exists($funcionario,'getCpfFormatadoAttribute')
                    ? $funcionario->cpf_formatado
                    : $funcionario->cpf }}
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Empresa</span>
            <span class="detail-value">
                {{ $funcionario->empresa->nome ?? '—' }}
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="detail-value">
                @if($funcionario->ativo)
                    <span class="status status-finalizado">Ativo</span>
                @else
                    <span class="status status-cancelado">Inativo</span>
                @endif
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Criado em</span>
            <span class="detail-value">
                {{ optional($funcionario->created_at)->format('d/m/Y H:i') }}
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Atualizado em</span>
            <span class="detail-value">
                {{ optional($funcionario->updated_at)->format('d/m/Y H:i') }}
            </span>
        </div>

    </div>

    {{-- DOCUMENTOS --}}
    <div class="documents-box">
        <h3 class="documents-title">Documentos</h3>

        <ul class="file-list">
            @forelse ($funcionario->documentos as $documento)
                <li class="file-item">
                    <a href="{{ route('funcionarios.documentos.download', [$funcionario, $documento]) }}"
                       target="_blank"
                       class="file-name">
                        📄 {{ $documento->nome_original ?? basename($documento->nome_arquivo ?? 'arquivo') }}
                    </a>

                    <span class="file-date">
                        {{ $documento->updated_at->format('d/m/Y H:i') }}
                    </span>
                </li>
            @empty
                <li class="file-item muted">
                    Nenhum documento anexado.
                </li>
            @endforelse
        </ul>
    </div>

    {{-- AÇÕES --}}
    <div class="form-actions">
        <a href="{{ route('funcionarios.index') }}" class="btn btn-cancelar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

        @if(auth()->user()->permissao !== 'Consulta')
<a href="{{ route('funcionarios.edit', $funcionario->id) }}" class="btn btn-salvar">
            <i class="fa-solid fa-pen"></i>
            Editar
        </a>
@endif
    </div>

</div>
@endsection
