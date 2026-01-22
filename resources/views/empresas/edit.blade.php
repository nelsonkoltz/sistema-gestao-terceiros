@extends('layouts.app')

<head>
    <link rel="stylesheet" href="{{ asset('css/empresas/edit.css') }}">
</head>

@section('content')
<div class="container">
    <h1>Editar Empresa</h1>

    <div class="card mt-4">
        <div class="card-body">

            <!-- ===================== -->
            <!-- Formulário principal -->
            <!-- ===================== -->
            <form action="{{ route('empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Dados da Empresa -->
                <div class="details-section">
                    <div class="form-grid">
                        {{-- Campos da empresa --}}
                        <div class="form-group">
                            <label for="nome"><strong>Nome da Empresa</strong></label>
                            <input type="text" id="nome" name="nome" class="form-control"
                                   value="{{ old('nome', $empresa->nome) }}" required>
                            @error('nome') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label for="cnpj"><strong>CNPJ/CPF</strong></label>
                            <input type="text" id="cnpj" name="cnpj" class="form-control"
                                   value="{{ old('cnpj', $empresa->cnpj) }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="email"><strong>Email</strong></label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="{{ old('email', $empresa->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="telefone"><strong>Telefone</strong></label>
                            <input type="tel" id="telefone" name="telefone" class="form-control"
                                   value="{{ old('telefone', $empresa->telefone) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco_rua"><strong>Rua</strong></label>
                            <input type="text" id="endereco_rua" name="endereco_rua" class="form-control"
                                   value="{{ old('endereco_rua', $empresa->endereco_rua) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco_numero"><strong>Número</strong></label>
                            <input type="text" id="endereco_numero" name="endereco_numero" class="form-control"
                                   value="{{ old('endereco_numero', $empresa->endereco_numero) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco_bairro"><strong>Bairro</strong></label>
                            <input type="text" id="endereco_bairro" name="endereco_bairro" class="form-control"
                                   value="{{ old('endereco_bairro', $empresa->endereco_bairro) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco_cidade"><strong>Cidade</strong></label>
                            <input type="text" id="endereco_cidade" name="endereco_cidade" class="form-control"
                                   value="{{ old('endereco_cidade', $empresa->endereco_cidade) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco_estado"><strong>Estado</strong></label>
                            <input type="text" id="endereco_estado" name="endereco_estado" class="form-control"
                                   value="{{ old('endereco_estado', $empresa->endereco_estado) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco_cep"><strong>CEP</strong></label>
                            <input type="text" id="endereco_cep" name="endereco_cep" class="form-control"
                                   value="{{ old('endereco_cep', $empresa->endereco_cep) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Upload de novos documentos -->
                <div class="form-group mt-4">
                    <label for="documentos"><strong>Adicionar novos documentos (PDF/JPG/PNG)</strong></label>
                    <input type="file" id="documentos" name="documentos[]" class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png" multiple>
                </div>

                <!-- Botões principais -->
                <div class="form-group text-center mt-4">
                    <a href="{{ route('empresas.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form> <!-- ✅ Fecha o form principal AQUI -->

            <!-- =========================== -->
            <!-- Documentos Existentes -->
            <!-- =========================== -->
            <div class="form-group mt-5">
                <h5>Documentos Existentes:</h5>
                <ul class="list-group">
                    @forelse ($empresa->documentos as $documento)
                        <li class="list-group-item d-flex justify-content-between align-items-center file-item">
                            <span>
                                <a href="{{ asset('storage/' . $documento->caminho_arquivo) }}" target="_blank">
                                    {{ basename($documento->nome_arquivo) }}
                                </a>
                            </span>

                            <!-- Formulário independente de exclusão -->
                            <form action="{{ route('empresas.deleteDocumento', ['empresa' => $empresa->id, 'documento' => $documento->id]) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Deseja realmente remover este documento?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Nenhum documento enviado.</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</div>
@endsection
