@extends('layouts.app')

@section('title', 'Detalhes da Empresa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/empresas/show.css') }}?v={{ time() }}">
@endpush

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Detalhes da Empresa</h1>
        <p class="form-subtitle">
            Visualização completa das informações cadastradas.
        </p>
    </header>

    {{-- DADOS --}}
    <div class="details-grid">

        <div class="detail-item">
            <span class="detail-label">Nome / Razão Social</span>
            <span class="detail-value">{{ $empresa->nome }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">CPF / CNPJ</span>
            <span class="detail-value">{{ $empresa->cnpj }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">E-mail</span>
            <span class="detail-value">{{ $empresa->email }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Telefone</span>
            <span class="detail-value">{{ $empresa->telefone }}</span>
        </div>

        <div class="detail-item full">
            <span class="detail-label">Endereço</span>
            <span class="detail-value">
                {{ $empresa->endereco_rua }}, {{ $empresa->endereco_numero }} –
                {{ $empresa->endereco_bairro }},
                {{ $empresa->endereco_cidade }}/{{ $empresa->endereco_estado }}
                – CEP {{ $empresa->endereco_cep }}
            </span>
        </div>

    </div>

    {{-- DOCUMENTOS --}}
    <div class="documents-box">
        <h3 class="documents-title">Documentos</h3>

        <ul class="file-list">
            @forelse ($empresa->documentos as $documento)
                @php
                    $filePath = Str::startsWith($documento->caminho_arquivo, 'public/')
                        ? Str::replaceFirst('public/', '', $documento->caminho_arquivo)
                        : $documento->caminho_arquivo;
                @endphp

                <li class="file-item">
                    <a href="{{ asset('storage/' . $filePath) }}"
                       target="_blank"
                       class="file-name">
                        📄 {{ $documento->nome_arquivo }}
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
        <a href="{{ route('empresas.index') }}" class="btn btn-cancelar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

        <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-salvar">
            <i class="fa-solid fa-pen"></i>
            Editar
        </a>
    </div>

</div>
@endsection
