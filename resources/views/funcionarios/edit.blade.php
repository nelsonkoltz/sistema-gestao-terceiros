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

            {{-- EMPRESA (SOMENTE UI) --}}
            <div class="form-group full">
                <label>Empresa</label>

                <input type="text"
                       id="empresa_nome"
                       class="form-control"
                       list="lista-empresas"
                       autocomplete="off"
                       value="{{ $funcionario->empresa->nome ?? '' }}"
                       placeholder="Digite para buscar a empresa"
                       required>

                <datalist id="lista-empresas"></datalist>

                <input type="hidden"
                       id="empresa_id"
                       name="empresa_id"
                       value="{{ old('empresa_id', $funcionario->empresa_id) }}">

                <small>Selecione uma empresa válida da lista.</small>
            </div>

            {{-- STATUS --}}
            <div class="form-group">
                <label>Status</label>
                <select name="ativo" class="form-control">
                    <option value="1" {{ old('ativo', $funcionario->ativo) == 1 ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ old('ativo', $funcionario->ativo) == 0 ? 'selected' : '' }}>Inativo</option>
                </select>
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

                <ul id="preview-list" class="file-list"></ul>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('funcionarios.index') }}" class="btn btn-cancelar">
                Voltar
            </a>

            <button type="submit" class="btn btn-salvar">
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
                    <a href="{{ route('funcionarios.documentos.download', [$funcionario, $doc]) }}"
                       target="_blank"
                       class="file-name">
                        {{ $doc->nome_original }}
                    </a>

                    <form action="{{ route('funcionarios.documentos.destroy', [$funcionario->id, $doc->id]) }}"
                          method="POST"
                          onsubmit="return confirm('Deseja realmente remover este documento?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="file-remove" title="Excluir">
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
    if (window.Inputmask) new Inputmask('999.999.999-99').mask(document.getElementById('cpf'));

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

    // Autocomplete empresas
    const input = document.getElementById('empresa_nome');
    const hidden = document.getElementById('empresa_id');
    const datalist = document.getElementById('lista-empresas');
    let cache = [];

    async function buscarEmpresas(q) {
        const res = await fetch(`{{ route('empresas.search') }}?q=${encodeURIComponent(q)}`);
        return res.ok ? await res.json() : [];
    }

    input.addEventListener('input', async () => {
        const q = input.value.trim();
        hidden.value = '';
        datalist.innerHTML = '';
        if (q.length < 2) return;

        try { cache = await buscarEmpresas(q); } catch (_) { cache = []; }
        if (input.value.trim() !== q) return;
        datalist.replaceChildren(...cache.map(e => {
            const option = document.createElement('option');
            option.value = e.nome;
            return option;
        }));
    });

    input.addEventListener('change', () => {
        const val = input.value.toLowerCase();
        const found = cache.find(e => e.nome.toLowerCase() === val);
        hidden.value = found ? found.id : '';
    });
});
</script>
@endsection
