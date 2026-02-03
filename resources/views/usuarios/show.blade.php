@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/usuarios/show.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Detalhes do Usuário</h1>
        <p class="form-subtitle">
            Visualização completa das informações cadastradas.
        </p>
    </header>

    {{-- DADOS --}}
    <div class="details-grid">

        <div class="detail-item">
            <span class="detail-label">Nome</span>
            <span class="detail-value">{{ $usuario->name }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Usuário</span>
            <span class="detail-value">{{ $usuario->username }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Setor</span>
            <span class="detail-value">{{ $usuario->setor }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Permissão</span>
            <span class="detail-value">
                <span class="status status-{{ strtolower($usuario->permissao) }}">
                    {{ $usuario->permissao }}
                </span>
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">E-mail</span>
            <span class="detail-value">
                {{ $usuario->email ?? '—' }}
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Criado em</span>
            <span class="detail-value">
                {{ optional($usuario->created_at)->format('d/m/Y H:i') }}
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Atualizado em</span>
            <span class="detail-value">
                {{ optional($usuario->updated_at)->format('d/m/Y H:i') }}
            </span>
        </div>

    </div>

    {{-- AÇÕES --}}
    <div class="form-actions">
        <a href="{{ route('usuarios.index') }}" class="btn btn-cancelar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-salvar">
            <i class="fa-solid fa-pen"></i>
            Editar
        </a>
    </div>

</div>
@endsection
