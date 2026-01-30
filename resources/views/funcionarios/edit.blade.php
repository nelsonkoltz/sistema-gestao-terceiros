@extends('layouts.app')

@section('title', 'Editar Funcionário')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/funcionarios/edit.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Editar Funcionário</h1>
        <p class="form-subtitle">
            Atualize os dados do funcionário e gerencie seus documentos.
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
    <form action="{{ route('funcionarios.update', $funcionario->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- GRID --}}
        <div class="form-grid">

            {{-- NOME --}}
            <div class="form-group">
                <label>Nome</label>
                <input type="text"
                       name="nome"
                       class="form-control"
                       value="{{ old('nome', $funcionario->nome) }}"
                       required>
            </div>

            {{-- CPF --}}
            <div class="form-group">
                <label>CPF</label>
                <input type="text"
                       id="cpf"
                       name="cpf"
                       class="form-control"
                       value="{{ old('cpf', $funcionario->cpf) }}"
                       required>
            </div>

            {{-- EMPRESA --}}
            <div class="form-group">
                <label>Empresa</label>
                <input type="text"
                       id="empresa_nome"
                       name="empresa_nome"
                       class="form-control"
                       list="lista-empresas"
                       autocomplete="off"
                       value="{{ old('empresa_nome', $funcionario->empresa->nome ?? '') }}"
                       required>

                <datalist id="lista-empresas"></datalist>

                <input type="hidden"
                       id="empresa_id"
                       name="empresa_id"
                       value="{{ old('empresa_id', $funcionario->empresa_id) }}">
            </div>

            {{-- STATUS --}}
            <div class="form-group">
                <label>Status</label>
                <div class="checkbox-wrapper">
                    <input type="checkbox"
                           id="ativo"
                           name="ativo"
                           {{ old('ativo', $funcionario->ativo) ? 'checked' : '' }}>
                    <label for="ativo">Ativo</label>
                </div>
            </div>

            {{-- NOVOS DOCUMENTOS --}}
            <div class="form-group full">
                <label>Adicionar novos documentos</label>
                <input type="file"
                       name="documentos[]"
                       class="form-control"
                       multiple
                       accept=".pdf,.jpg,.jpeg,.png">

                <small>PDF, JPG ou PNG • até 5MB por arquivo</small>

                <ul id="preview-list" class="file-preview"></ul>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('funcionarios.index') }}" class="btn btn-cancelar">
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
            @forelse ($funcionario->documentos as $doc)
                <li class="file-item">
                    <a href="{{ asset('storage/' . $doc->caminho) }}"
                       target="_blank"
                       class="file-name">
                        {{ $doc->nome }}
                    </a>

                    <form action="{{ route('funcionarios.documentos.destroy', [$funcionario->id, $doc->id]) }}"
                          method="POST"
                          onsubmit="return confirm('Deseja realmente remover este documento?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="file-remove"
                                title="Excluir">
                            ✕
                        </button>
                    </form>
                </li>
            @empty
                <li class="file-item muted">
                    Nenhum documento cadastrado.
                </li>
            @endforelse
        </ul>
    </div>

</div>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/inputmask/dist/inputmask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Máscara CPF
    new Inputmask('999.999.999-99').mask(document.getElementById('cpf'));

    // Preview documentos
    const inputDocs = document.querySelector('input[name="documentos[]"]');
    const previewList = document.getElementById('preview-list');

    if (inputDocs) {
        inputDocs.addEventListener('change', () => {
            previewList.innerHTML = '';
            [...inputDocs.files].forEach(file => {
                const li = document.createElement('li');
                li.textContent = file.name;
                previewList.appendChild(li);
            });
        });
    }

    // Autocomplete empresa
    const input = document.getElementById('empresa_nome');
    const hidden = document.getElementById('empresa_id');
    const datalist = document.getElementById('lista-empresas');
    let cache = [];

    async function buscarEmpresas(q) {
        const res = await fetch(`{{ route('empresas.search') }}?q=${encodeURIComponent(q)}`);
        return res.ok ? await res.json() : [];
    }

    input.addEventListener('input', async e => {
        const q = e.target.value.trim();
        hidden.value = '';
        datalist.innerHTML = '';
        if (q.length < 2) return;

        cache = await buscarEmpresas(q);
        datalist.innerHTML = cache.map(c =>
            `<option value="${c.nome}"></option>`
        ).join('');
    });

    input.addEventListener('change', () => {
        const val = input.value.toLowerCase();
        const found = cache.find(c => c.nome.toLowerCase() === val);
        hidden.value = found ? found.id : '';
    });
});
</script>
@endsection
