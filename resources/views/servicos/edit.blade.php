@extends('layouts.app')

@section('title', 'Editar Serviço')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-form.css') }}?v={{ filemtime(public_path('css/edit-form.css')) }}">
@endpush

@section('content')
    <div class="form-container">

        {{-- HEADER --}}
        <header class="form-header">
            <h1 class="form-title">Editar Serviço</h1>
            <p class="form-subtitle">
                Atualize as informações do serviço abaixo.
            </p>
        </header>

        @if ($errors->any())
            <div class="alert-error" role="alert">
                <strong>Revise os campos destacados:</strong>
                <ul>@foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- MENSAGEM DE SUCESSO --}}
        @if (session('success'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif


        <form action="{{ route('servicos.update', $servico->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">

                {{-- EMPRESA --}}
                <div class="form-group">
                    <label for="empresa_id">Empresa</label>
                    <select id="empresa_id" name="empresa_id" class="form-control" required>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ old('empresa_id', $servico->empresa_id) == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SETOR --}}
                <div class="form-group">
                    <label for="setor_id">Setor</label>
                    <select id="setor_id" name="setor_id" class="form-control" required>
                        <option value="">Selecione um setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" {{ old('setor_id', $servico->setor_id) == $setor->id ? 'selected' : '' }}>
                                {{ $setor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DATA DO SERVIÇO --}}
                <div class="form-group">
                    <label for="data_servico">Data do Serviço</label>
                    <input type="date" id="data_servico" name="data_servico" class="form-control"
                        value="{{ old('data_servico', optional($servico->data_servico)->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label for="hora_inicio">Horário de entrada</label>
                    <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" value="{{ old('hora_inicio', substr((string) $servico->hora_inicio, 0, 5) ?: '08:00') }}" required>
                </div>

                <div class="form-group">
                    <label for="hora_fim">Horário de saída</label>
                    <input type="time" id="hora_fim" name="hora_fim" class="form-control" value="{{ old('hora_fim', substr((string) $servico->hora_fim, 0, 5) ?: '18:00') }}" required>
                </div>

                {{-- ALMOÇO --}}
                <div class="form-group">
                    <label for="vai_almocar">O terceiro vai almoçar?</label>
                    <select id="vai_almocar" name="vai_almocar" class="form-control" required>
                        <option value="1" {{ old('vai_almocar', $servico->vai_almocar) == 1 ? 'selected' : '' }}>Sim</option>
                        <option value="0" {{ old('vai_almocar', $servico->vai_almocar) == 0 ? 'selected' : '' }}>Não</option>
                    </select>
                </div>

                {{-- DESCRIÇÃO --}}
                <div class="form-group full">
                    <label for="descricao">Descrição do Serviço</label>
                    <textarea id="descricao" name="descricao" class="form-control"
                        required>{{ old('descricao', $servico->descricao) }}</textarea>
                </div>

               {{-- STATUS --}}
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Agendado"
                            {{ old('status', $servico->status) == 'Agendado' ? 'selected' : '' }}>
                            Agendado
                        </option>

                        <option value="Em Andamento"
                            {{ old('status', $servico->status) == 'Em Andamento' ? 'selected' : '' }}>
                            Em Andamento
                        </option>

                        <option value="Finalizado"
                            {{ old('status', $servico->status) == 'Finalizado' ? 'selected' : '' }}>
                            Finalizado
                        </option>

                        <option value="Cancelado"
                            {{ old('status', $servico->status) == 'Cancelado' ? 'selected' : '' }}>
                            Cancelado
                        </option>
                    </select>
                </div>

            </div>

            {{-- AÇÕES --}}
            <div class="form-actions">
                <a href="{{ route('servicos.show', $servico) }}" class="btn btn-cancelar">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar
                </a>

                <button type="submit" class="btn btn-salvar">
                    <i class="fa-solid fa-rotate"></i>
                    Salvar alterações
                </button>
            </div>

        </form>
    </div>
@endsection
