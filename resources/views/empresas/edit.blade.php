@extends('layouts.app')

@section('title', 'Editar Empresa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/empresas/edit.css') }}?v={{ time() }}">
@endpush

@section('content')
    <div class="form-container">

        {{-- HEADER --}}
        <header class="form-header">
            <h1 class="form-title">Editar Empresa</h1>
            <p class="form-subtitle">
                Atualize os dados da empresa e gerencie seus documentos.
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

        {{-- FORM --}}
        <form action="{{ route('empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- GRID --}}
            <div class="form-grid">

                <div class="form-group">
                    <label>Nome / Razão Social</label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $empresa->nome) }}" required>
                </div>

                <div class="form-group">
                    <label>CPF / CNPJ</label>
                    <input type="text" class="form-control" value="{{ $empresa->cnpj }}" readonly>
                </div>

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}"
                        required>
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control"
                        value="{{ old('telefone', $empresa->telefone) }}" required>
                </div>

                <div class="form-group">
                    <label>Rua</label>
                    <input type="text" name="endereco_rua" class="form-control"
                        value="{{ old('endereco_rua', $empresa->endereco_rua) }}" required>
                </div>

                <div class="form-group">
                    <label>Número</label>
                    <input type="text" name="endereco_numero" class="form-control"
                        value="{{ old('endereco_numero', $empresa->endereco_numero) }}" required>
                </div>

                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" name="endereco_bairro" class="form-control"
                        value="{{ old('endereco_bairro', $empresa->endereco_bairro) }}" required>
                </div>

                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="endereco_cidade" class="form-control"
                        value="{{ old('endereco_cidade', $empresa->endereco_cidade) }}" required>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" name="endereco_estado" class="form-control"
                        value="{{ old('endereco_estado', $empresa->endereco_estado) }}" required>
                </div>

                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="endereco_cep" class="form-control"
                        value="{{ old('endereco_cep', $empresa->endereco_cep) }}" required>
                </div>

                {{-- NOVOS DOCUMENTOS --}}
                <div class="form-group full">
                    <label>Adicionar novos documentos</label>
                    <input type="file" name="documentos[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
                    <small>PDF, JPG ou PNG • até 20MB por arquivo</small>
                </div>

            </div>

            {{-- AÇÕES --}}
            <div class="form-actions">
                <a href="{{ route('empresas.index') }}" class="btn btn-cancelar">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar
                </a>

                <button type="submit" class="btn btn-salvar">
                    <i class="fa-solid fa-check"></i>
                    Atualizar
                </button>
            </div>

        </form>

        {{-- DOCUMENTOS EXISTENTES --}}
        <div class="documents-box">
            <h3 class="documents-title">Documentos Existentes</h3>

            <ul class="file-list">
                @forelse ($empresa->documentos as $documento)
                    <li class="file-item">
                        <a href="{{ asset('storage/' . $documento->caminho_arquivo) }}" target="_blank" class="file-name">
                            {{ $documento->nome_arquivo }}
                        </a>

                        <form action="{{ route('empresas.deleteDocumento', [$empresa->id, $documento->id]) }}" method="POST"
                            onsubmit="return confirm('Deseja realmente remover este documento?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="file-remove" title="Excluir">
                                ✕
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="file-item muted">Nenhum documento cadastrado.</li>
                @endforelse
            </ul>
        </div>

    </div>
@endsection