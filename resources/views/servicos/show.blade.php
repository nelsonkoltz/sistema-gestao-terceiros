@extends('layouts.app')
@section('title', 'Detalhes do Serviço')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/servicos/show.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="servicos-form">
    <div class="form-container">

        {{-- HEADER --}}
        <header class="form-header">
            <h1 class="form-title">Detalhes do Serviço</h1>
            <p class="form-subtitle">
                Visualização completa das informações do serviço.
            </p>
        </header>

        {{-- GRID --}}
        <div class="details-grid">

            <div class="detail-item full">
                <label>Descrição do Serviço</label>
                <div class="detail-value">
                    {{ $servico->descricao }}
                </div>
            </div>

            <div class="detail-item">
                <label>Empresa</label>
                <div class="detail-value">
                    {{ $servico->empresa->nome ?? '-' }}
                </div>
            </div>

            <div class="detail-item">
                <label>Solicitante</label>
                <div class="detail-value">
                    {{ $servico->solicitante->name ?? '-' }}
                </div>
            </div>

            <div class="detail-item">
                <label>Vai Almoçar?</label>
                <span class="badge {{ $servico->vai_almocar ? 'badge-success' : 'badge-danger' }}">
                    {{ $servico->vai_almocar ? 'Sim' : 'Não' }}
                </span>
            </div>

            <div class="detail-item">
                <label>Status</label>
                <span class="badge status-{{ strtolower(str_replace(' ', '-', $servico->status)) }}">
                    {{ $servico->status }}
                </span>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('servicos.index') }}" class="btn btn-cancelar">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>
        </div>

    </div>
</div>
@endsection
