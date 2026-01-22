@extends('layouts.app')
@section('title', 'Cadastrar Novo Funcionário')

<head>
    {{-- CSS personalizado (salve em public/css/funcionarios/create.css) --}}
    <link href="{{ asset('css/funcionarios/create.css') }}" rel="stylesheet">
</head>

@section('content')
<div class="container form-container">
    <h1 class="text-center mb-4">Cadastrar Novo Funcionário</h1>

    {{-- Mensagem de sucesso --}}
    @if (session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    {{-- Exibição de erros --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário --}}
    <form action="{{ route('funcionarios.store') }}" method="POST" enctype="multipart/form-data" class="employee-form">
        @csrf

        <div class="row">
            {{-- Nome --}}
            <div class="col-md-6 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" class="form-control"
                       value="{{ old('nome') }}" placeholder="Digite o nome completo" required>
            </div>

            {{-- CPF --}}
            <div class="col-md-6 mb-3">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control"
                       value="{{ old('cpf') }}" placeholder="999.999.999-99" required>
            </div>

            {{-- Empresa (autocomplete) --}}
            <div class="col-md-8 mb-3">
                <label for="empresa_nome" class="form-label">Empresa (digite e selecione)</label>
                <input type="text" id="empresa_nome" name="empresa_nome" class="form-control"
                       list="lista-empresas" autocomplete="off"
                       value="{{ old('empresa_nome') }}" placeholder="Ex: Costa Rica Malhas" required>
                <datalist id="lista-empresas"></datalist>
                <input type="hidden" name="empresa_id" id="empresa_id" value="{{ old('empresa_id') }}">
                <small class="help-text text-muted">
                    Comece a digitar para buscar a empresa. Ao selecionar, o vínculo é feito automaticamente.
                </small>
                @error('empresa_nome')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            {{-- Status --}}
            <div class="col-md-4 mb-3">
                <label class="form-label d-block">Status</label>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="ativo" name="ativo"
                           {{ old('ativo', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ativo">Ativo</label>
                </div>
            </div>

            {{-- Documentos --}}
            <div class="col-md-12 mb-3">
                <label for="documentos" class="form-label">Documentos (PDF/JPG/PNG) — opcional</label>
                <input type="file" name="documentos[]" id="documentos" class="form-control"
                       multiple accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text text-muted">
                    Formatos permitidos: <strong>PDF, JPG, JPEG, PNG</strong> (máx. 5MB por arquivo).
                </small>
            </div>
        </div>

        {{-- Botões --}}
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-primary">Cadastrar Funcionário</button>
        </div>
    </form>
</div>

{{-- Scripts: Máscara CPF e Autocomplete de Empresas --}}
<script src="https://cdn.jsdelivr.net/npm/inputmask/dist/inputmask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Máscara CPF
    const imCPF = new Inputmask('999.999.999-99');
    imCPF.mask(document.getElementById('cpf'));

    // Autocomplete de empresas
    const input = document.getElementById('empresa_nome');
    const hidden = document.getElementById('empresa_id');
    const datalist = document.getElementById('lista-empresas');
    let cache = [];

async function buscarEmpresas(q) {
    const url = "{{ url('empresas/search') }}?q=" + encodeURIComponent(q);
    console.log("Buscando:", url);
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) return [];
    return await res.json();
}


    input.addEventListener('input', async (e) => {
        const q = e.target.value.trim();
        hidden.value = '';
        datalist.innerHTML = '';
        if (q.length < 2) return;
        cache = await buscarEmpresas(q);
        datalist.innerHTML = cache.map(c => `<option data-id="${c.id}" value="${c.nome}"></option>`).join('');
    });

    input.addEventListener('change', () => {
        const val = input.value.trim().toLowerCase();
        const found = cache.find(c => c.nome.toLowerCase() === val);
        hidden.value = found ? found.id : '';
    });

    // Feedback visual ao enviar formulário
    const form = document.querySelector('form');
    form.addEventListener('submit', () => {
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Enviando...';
    });
});
</script>
@endsection
