@extends('layouts.app')

<head>
    <link rel="stylesheet" href="{{ asset('css/empresas/create.css') }}">
</head>

@section('content')
<div class="container form-container">
    <h1 class="text-center">Cadastrar Nova Empresa</h1>

    <!-- Mensagem de sucesso -->
    @if (session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    <!-- Exibição de erros -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulário -->
    <form action="{{ route('empresas.store') }}" 
          method="POST" 
          enctype="multipart/form-data" 
          class="company-form">
        @csrf

        <div class="row">
            <!-- Nome -->
            <div class="col-md-6 mb-3">
                <label for="nome" class="form-label"><strong>Nome da Empresa</strong></label>
                <input type="text" name="nome" id="nome" class="form-control" 
                       value="{{ old('nome') }}" required>
            </div>

            <!-- Tipo -->
            <div class="col-md-6 mb-3">
                <label for="tipo" class="form-label"><strong>Tipo de Cadastro</strong></label>
                <select name="tipo" id="tipo" class="form-control" required>
                    <option value="CNPJ" {{ old('tipo') == 'CNPJ' ? 'selected' : '' }}>Pessoa Jurídica (CNPJ)</option>
                    <option value="CPF" {{ old('tipo') == 'CPF' ? 'selected' : '' }}>Pessoa Física (CPF)</option>
                </select>
            </div>

            <!-- CNPJ / CPF -->
            <div class="col-md-6 mb-3">
                <label for="cnpj" class="form-label"><strong>CNPJ / CPF</strong></label>
                <input type="text" name="cnpj" id="cnpj" class="form-control" 
                       value="{{ old('cnpj') }}" required>
            </div>

            <!-- Telefone -->
            <div class="col-md-6 mb-3">
                <label for="telefone" class="form-label"><strong>Telefone</strong></label>
                <input type="text" name="telefone" id="telefone" class="form-control" 
                       value="{{ old('telefone') }}" required>
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label"><strong>E-mail</strong></label>
                <input type="email" name="email" id="email" class="form-control" 
                       value="{{ old('email') }}" required>
            </div>

            <!-- Endereço -->
            <div class="col-md-6 mb-3">
                <label for="endereco_rua" class="form-label"><strong>Rua</strong></label>
                <input type="text" name="endereco_rua" id="endereco_rua" class="form-control" 
                       value="{{ old('endereco_rua') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="endereco_numero" class="form-label"><strong>Número</strong></label>
                <input type="text" name="endereco_numero" id="endereco_numero" class="form-control" 
                       value="{{ old('endereco_numero') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="endereco_bairro" class="form-label"><strong>Bairro</strong></label>
                <input type="text" name="endereco_bairro" id="endereco_bairro" class="form-control" 
                       value="{{ old('endereco_bairro') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="endereco_cidade" class="form-label"><strong>Cidade</strong></label>
                <input type="text" name="endereco_cidade" id="endereco_cidade" class="form-control" 
                       value="{{ old('endereco_cidade') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="endereco_estado" class="form-label"><strong>Estado</strong></label>
                <input type="text" name="endereco_estado" id="endereco_estado" class="form-control" 
                       value="{{ old('endereco_estado') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="endereco_cep" class="form-label"><strong>CEP</strong></label>
                <input type="text" name="endereco_cep" id="endereco_cep" class="form-control" 
                       value="{{ old('endereco_cep') }}" required>
            </div>

            <!-- Documentos -->
            <div class="col-md-12 mb-3">
                <label for="documentos" class="form-label"><strong>Documentos (PDF/JPG/PNG)</strong></label>
                <input type="file" name="documentos[]" id="documentos" class="form-control" 
                       accept=".pdf,.jpg,.jpeg,.png" multiple>
                <small class="text-muted d-block mt-1">
                    Você pode anexar mais de um arquivo (máx. 20MB por arquivo)
                </small>
                <ul id="preview-list" class="list-group mt-2"></ul>
            </div>
        </div>

        <!-- Botões -->
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('empresas.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-primary">Cadastrar Empresa</button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/inputmask/dist/inputmask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Máscaras CPF/CNPJ
    const imCNPJ = new Inputmask('99.999.999/9999-99');
    const imCPF = new Inputmask('999.999.999-99');
    const cnpjInput = document.getElementById('cnpj');
    const tipoInput = document.getElementById('tipo');

    function mudarMascara(tipo) {
        if (tipo === 'CPF') {
            cnpjInput.value = '';
            imCPF.mask(cnpjInput);
            cnpjInput.placeholder = 'Insira o CPF';
        } else {
            cnpjInput.value = '';
            imCNPJ.mask(cnpjInput);
            cnpjInput.placeholder = 'Insira o CNPJ';
        }
    }

    mudarMascara(tipoInput.value);
    tipoInput.addEventListener('change', e => mudarMascara(e.target.value));

    // Preview dos arquivos
    const inputDocs = document.getElementById('documentos');
    const previewList = document.getElementById('preview-list');
    inputDocs.addEventListener('change', () => {
        previewList.innerHTML = '';
        [...inputDocs.files].forEach(file => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.textContent = file.name;
            previewList.appendChild(li);
        });
    });
});
</script>
@endsection
