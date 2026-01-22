@extends('layouts.app')

@section('title', 'Cadastrar Serviço')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/servicos/create.css') }}?v={{ time() }}">
@endpush


@section('content')
    <div class="form-container">

        {{-- HEADER --}}
        <header class="form-header">
            <h1 class="form-title">Cadastrar Serviço</h1>
            <p class="form-subtitle">
                Preencha as informações abaixo para registrar um novo serviço.
            </p>
        </header>

        {{-- ERROS --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form action="{{ route('servicos.store') }}" method="POST">
            @csrf

            {{-- GRID --}}
            <div class="form-grid">

                {{-- EMPRESA --}}
                <div class="form-group">
                    <label for="empresa_id">Empresa</label>
                    <select id="empresa_id" name="empresa_id" class="form-control" required>
                        <option value="">Selecione uma empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- SETOR --}}
                <div class="form-group">
                    <label for="setor_id">Setor</label>
                    <select id="setor_id" name="setor_id" class="form-control" required>
                        <option value="">Selecione um setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- DESCRIÇÃO --}}
                <div class="form-group full">
                    <label for="descricao">Descrição do Serviço</label>
                    <textarea id="descricao" name="descricao" class="form-control" required></textarea>
                </div>

                {{-- DATA SERVIÇO --}}
                <div class="form-group">
                    <label for="data_servico">Data do Serviço</label>
                    <input type="date" id="data_servico" name="data_servico" class="form-control" required>
                </div>

                {{-- ALMOÇO --}}
                <div class="form-group">
                    <label for="vai_almocar">O terceiro vai almoçar?</label>
                    <select id="vai_almocar" name="vai_almocar" class="form-control" required>
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Pendente">Pendente</option>
                        <option value="Em Andamento">Em Andamento</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                {{-- DATA CONCLUSÃO --}}
                <div class="form-group full" id="data_conclusao_container" style="display:none;">
                    <label for="data_conclusao">Data de Conclusão</label>
                    <input type="date" id="data_conclusao" name="data_conclusao" class="form-control">
                </div>

            </div>

            {{-- AÇÕES --}}
            <div class="form-actions">
                <a href="{{ route('servicos.index') }}" class="btn btn-cancelar">
                    <i class="fa-solid fa-arrow-left"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn btn-salvar">
                    <i class="fa-solid fa-check"></i>
                    Salvar
                </button>
            </div>

        </form>
    </div>

    {{-- SCRIPT STATUS --}}
    <script>
        const statusSelect = document.getElementById('status');
        const dataConclusaoContainer = document.getElementById('data_conclusao_container');

        statusSelect.addEventListener('change', function () {
            dataConclusaoContainer.style.display =
                this.value === 'Finalizado' ? 'block' : 'none';
        });
    </script>
@endsection