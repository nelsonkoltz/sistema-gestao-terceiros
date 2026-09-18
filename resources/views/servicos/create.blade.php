@extends('layouts.app')

@section('title', 'Nova Solicitação de Serviço')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/servicos/create.css') }}?v={{ filemtime(public_path('css/servicos/create.css')) }}">
@endpush

@section('content')
<div class="form-container">
    <header class="form-header">
        <div class="form-heading">
            <span class="form-icon" aria-hidden="true"><i class="bi bi-clipboard2-plus"></i></span>
            <div>
                <h1 class="form-title">Nova solicitação de serviço</h1>
                <p class="form-subtitle">Informe quem realizará o serviço, o setor responsável e a data prevista.</p>
            </div>
        </div>
    </header>

    @if ($errors->any())
        <div class="alert-error" role="alert" aria-live="polite">
            <strong>Revise os campos destacados:</strong>
            <ul>
                @foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('servicos.store') }}" method="POST" id="servico-form">
        @csrf
        <input type="hidden" name="status" value="Agendado">

        <div class="form-grid">
            <div class="section-heading full">
                <span>Dados da solicitação</span>
                <small>Os campos com * são obrigatórios.</small>
            </div>

            <div class="form-group">
                <label for="empresa_id">Empresa terceirizada <span>*</span></label>
                <div class="control-with-icon">
                    <i class="bi bi-building" aria-hidden="true"></i>
                    <select id="empresa_id" name="empresa_id"
                            class="form-control @error('empresa_id') is-invalid @enderror" required>
                        <option value="">Selecione uma empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ (string) old('empresa_id') === (string) $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('empresa_id')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="setor_id">Setor solicitante <span>*</span></label>
                <div class="control-with-icon">
                    <i class="bi bi-diagram-3" aria-hidden="true"></i>
                    <select id="setor_id" name="setor_id"
                            class="form-control @error('setor_id') is-invalid @enderror" required>
                        <option value="">Selecione um setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" {{ (string) old('setor_id') === (string) $setor->id ? 'selected' : '' }}>
                                {{ $setor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('setor_id')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group full">
                <div class="label-row">
                    <label for="descricao">Descrição do serviço <span>*</span></label>
                    <small id="contador-descricao">0 / 1000</small>
                </div>
                <textarea id="descricao" name="descricao"
                          class="form-control @error('descricao') is-invalid @enderror"
                          maxlength="1000" rows="5"
                          placeholder="Descreva o trabalho que será realizado, local e outras orientações importantes para a entrada."
                          aria-describedby="descricao-help contador-descricao" required>{{ old('descricao') }}</textarea>
                <small id="descricao-help">Uma descrição clara ajuda a guarita a confirmar o motivo da entrada.</small>
                @error('descricao')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="section-heading full section-spaced"><span>Agendamento</span></div>

            <div class="form-group">
                <label for="data_servico">Data do serviço <span>*</span></label>
                <div class="control-with-icon">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    <input type="date" id="data_servico" name="data_servico"
                           class="form-control @error('data_servico') is-invalid @enderror"
                           value="{{ old('data_servico') }}" min="{{ now()->format('Y-m-d') }}" required>
                </div>
                @error('data_servico')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="hora_inicio">Horário de entrada <span>*</span></label>
                <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" value="{{ old('hora_inicio', '08:00') }}" required>
                @error('hora_inicio')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="hora_fim">Horário de saída <span>*</span></label>
                <input type="time" id="hora_fim" name="hora_fim" class="form-control" value="{{ old('hora_fim', '18:00') }}" required>
                @error('hora_fim')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <fieldset class="form-group lunch-field">
                <legend>O terceiro utilizará o refeitório? <span>*</span></legend>
                <div class="choice-group">
                    <label class="choice-card">
                        <input type="radio" name="vai_almocar" value="1" {{ old('vai_almocar', '0') === '1' ? 'checked' : '' }}>
                        <span><i class="bi bi-check-circle"></i> Sim</span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="vai_almocar" value="0" {{ old('vai_almocar', '0') === '0' ? 'checked' : '' }}>
                        <span><i class="bi bi-x-circle"></i> Não</span>
                    </label>
                </div>
                @error('vai_almocar')<small class="field-error">{{ $message }}</small>@enderror
            </fieldset>

            <div class="status-card full">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <div>
                    <strong>Serviço agendado imediatamente</strong>
                    <span>Não existe etapa de aprovação. Na data e no horário informados, a guarita poderá realizar a liberação.</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('servicos.index') }}" class="btn btn-cancelar">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button type="submit" class="btn btn-salvar">
                <i class="bi bi-send-check"></i> Enviar solicitação
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const descricao = document.getElementById('descricao');
    const contador = document.getElementById('contador-descricao');
    const atualizarContador = () => contador.textContent = `${descricao.value.length} / 1000`;
    descricao.addEventListener('input', atualizarContador);
    atualizarContador();
});
</script>
@endsection
