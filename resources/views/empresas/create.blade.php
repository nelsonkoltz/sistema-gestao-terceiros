@extends('layouts.app')

@section('title', 'Cadastrar Empresa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/empresas/create.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Cadastrar Empresa</h1>
        <p class="form-subtitle">
            Preencha os dados abaixo para registrar uma nova empresa ou pessoa física.
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
    <form action="{{ route('empresas.store') }}" method="POST" enctype="multipart/form-data" id="empresa-form">
        @csrf

        <div class="form-grid">

            <div class="form-group">
                <label>Nome / Razão Social</label>
                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
            </div>

            <div class="form-group">
                <label>Tipo de Cadastro</label>
                <select name="tipo" id="tipo" class="form-control" required>
                    <option value="CNPJ">Pessoa Jurídica (CNPJ)</option>
                    <option value="CPF">Pessoa Física (CPF)</option>
                </select>
            </div>

            <div class="form-group">
                <label>CPF / CNPJ</label>
                <input type="text" name="cnpj" id="cnpj" class="form-control" value="{{ old('cnpj') }}" required>
            </div>

            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="telefone" class="form-control" value="{{ old('telefone') }}" required>
            </div>

            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label>Rua</label>
                <input type="text" name="endereco_rua" class="form-control" value="{{ old('endereco_rua') }}" required>
            </div>

            <div class="form-group">
                <label>Número</label>
                <input type="text" name="endereco_numero" class="form-control" value="{{ old('endereco_numero') }}" required>
            </div>

            <div class="form-group">
                <label>Bairro</label>
                <input type="text" name="endereco_bairro" class="form-control" value="{{ old('endereco_bairro') }}" required>
            </div>

            <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="endereco_cidade" class="form-control" value="{{ old('endereco_cidade') }}" required>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <input type="text" name="endereco_estado" class="form-control" value="{{ old('endereco_estado') }}" required>
            </div>

            <div class="form-group">
                <label>CEP</label>
                <input type="text" name="endereco_cep" class="form-control" value="{{ old('endereco_cep') }}" required>
            </div>

            {{-- DOCUMENTOS --}}
            <div class="form-group full">
                <label>Documentos</label>

                <input
                    type="file"
                    id="documentos"
                    class="form-control"
                    accept=".pdf,.jpg,.jpeg,.png"
                    multiple
                >

                <small>PDF, JPG ou PNG • até 20MB por arquivo</small>

                <ul id="lista-documentos" class="file-list"></ul>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('empresas.index') }}" class="btn btn-cancelar">
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

{{-- SCRIPT DOCUMENTOS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const input = document.getElementById('documentos');
    const lista = document.getElementById('lista-documentos');
    const form = document.getElementById('empresa-form');

    let arquivos = [];

    input.addEventListener('change', () => {
        for (const file of input.files) {
            arquivos.push(file);
        }

        atualizarLista();
        input.value = '';
    });

    function atualizarLista() {
        lista.innerHTML = '';

        arquivos.forEach((file, index) => {
            const li = document.createElement('li');
            li.className = 'file-item';

            li.innerHTML = `
                <span class="file-name">${file.name}</span>
                <button type="button" class="file-remove" data-index="${index}">✕</button>
            `;

            lista.appendChild(li);
        });
    }

    lista.addEventListener('click', (e) => {
        if (e.target.classList.contains('file-remove')) {
            arquivos.splice(e.target.dataset.index, 1);
            atualizarLista();
        }
    });

    form.addEventListener('submit', () => {
        const dataTransfer = new DataTransfer();
        arquivos.forEach(file => dataTransfer.items.add(file));

        input.files = dataTransfer.files;
        input.name = 'documentos[]';
    });

});
</script>
@endsection
