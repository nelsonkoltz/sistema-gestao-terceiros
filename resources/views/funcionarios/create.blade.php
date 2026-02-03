@extends('layouts.app')

@section('title', 'Cadastrar Funcionário')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/funcionarios/create.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Cadastrar Funcionário</h1>
        <p class="form-subtitle">
            Preencha os dados abaixo para registrar um novo funcionário.
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
    <form action="{{ route('funcionarios.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="funcionario-form">
        @csrf

        <div class="form-grid">

            {{-- NOME --}}
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text"
                       name="nome"
                       class="form-control"
                       value="{{ old('nome') }}"
                       required>
            </div>

            {{-- CPF --}}
            <div class="form-group">
                <label>CPF</label>
                <input type="text"
                       name="cpf"
                       id="cpf"
                       class="form-control"
                       value="{{ old('cpf') }}"
                       required>
            </div>

            {{-- EMPRESA (APENAS UI) --}}
            <div class="form-group full">
                <label>Empresa</label>

                <input type="text"
                       id="empresa_nome"
                       class="form-control"
                       list="lista-empresas"
                       autocomplete="off"
                       placeholder="Digite para buscar a empresa">

                <datalist id="lista-empresas"></datalist>

                {{-- CAMPO QUE REALMENTE IMPORTA --}}
                <input type="hidden"
                       name="empresa_id"
                       id="empresa_id"
                       value="{{ old('empresa_id') }}">

                <small>Digite e selecione uma empresa válida da lista.</small>
            </div>

            {{-- STATUS --}}
            <div class="form-group">
                <label>Status</label>
                <select name="ativo" class="form-control">
                    <option value="1" {{ old('ativo', 1) == 1 ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ old('ativo') == 0 ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>

            {{-- DOCUMENTOS --}}
            <div class="form-group full">
                <label>Documentos</label>

                <input type="file"
                       name="documentos[]"
                       class="form-control"
                       accept=".pdf,.jpg,.jpeg,.png"
                       multiple>

                <small>PDF, JPG ou PNG • até 5MB por arquivo</small>

                <ul id="lista-documentos" class="file-list"></ul>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('funcionarios.index') }}" class="btn btn-cancelar">
                Voltar
            </a>

            <button type="submit" class="btn btn-salvar">
                Salvar
            </button>
        </div>
    </form>
</div>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/inputmask/dist/inputmask.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* =========================
       MÁSCARA CPF
    ========================= */
    new Inputmask('999.999.999-99').mask(document.getElementById('cpf'));

    /* =========================
       AUTOCOMPLETE EMPRESAS
    ========================= */
    const inputEmpresa = document.getElementById('empresa_nome');
    const hiddenEmpresa = document.getElementById('empresa_id');
    const datalist = document.getElementById('lista-empresas');
    let cache = [];

    async function buscarEmpresas(q) {
        const res = await fetch("{{ url('empresas/search') }}?q=" + encodeURIComponent(q));
        return res.ok ? await res.json() : [];
    }

    inputEmpresa.addEventListener('input', async () => {
        const q = inputEmpresa.value.trim();
        hiddenEmpresa.value = '';
        datalist.innerHTML = '';

        if (q.length < 2) return;

        cache = await buscarEmpresas(q);
        datalist.innerHTML = cache
            .map(e => `<option value="${e.nome}"></option>`)
            .join('');
    });

    inputEmpresa.addEventListener('change', () => {
        const val = inputEmpresa.value.toLowerCase();
        const found = cache.find(e => e.nome.toLowerCase() === val);
        hiddenEmpresa.value = found ? found.id : '';
    });

    /* =========================
       BLOQUEIO DE SUBMIT INVÁLIDO
    ========================= */
    document.getElementById('funcionario-form')
        .addEventListener('submit', function (e) {

        if (!hiddenEmpresa.value) {
            e.preventDefault();
            alert('Selecione uma empresa válida da lista.');
            inputEmpresa.focus();
        }
    });

});
</script>
@endsection
