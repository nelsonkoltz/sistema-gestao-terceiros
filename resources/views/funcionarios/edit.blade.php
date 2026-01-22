@extends('layouts.app')
@section('title', 'Editar Funcionário')

<head>
  <link rel="stylesheet" href="{{ asset('css/funcionarios/edit.css') }}">
</head>

@section('content')
<div class="container">
    <h1>Editar Funcionário</h1>

    <div class="card mt-4">
        <div class="card-body">

            <!-- ===================== -->
            <!-- Formulário de Edição -->
            <!-- ===================== -->
            <form action="{{ route('funcionarios.update', $funcionario->id) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Dados do Funcionário -->
                <div class="details-section">
                    <div class="form-grid">
                        {{-- Nome --}}
                        <div class="form-group">
                            <label for="nome"><strong>Nome</strong></label>
                            <input type="text" id="nome" name="nome" class="form-control"
                                   value="{{ old('nome', $funcionario->nome) }}" required>
                            @error('nome') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- CPF --}}
                        <div class="form-group">
                            <label for="cpf"><strong>CPF</strong></label>
                            <input type="text" id="cpf" name="cpf" class="form-control"
                                   value="{{ old('cpf', $funcionario->cpf) }}"
                                   placeholder="999.999.999-99" required>
                            @error('cpf') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Empresa --}}
                        <div class="form-group">
                            <label for="empresa_nome"><strong>Empresa</strong></label>
                            <input type="text" id="empresa_nome" name="empresa_nome" class="form-control"
                                   list="lista-empresas" autocomplete="off"
                                   value="{{ old('empresa_nome', $funcionario->empresa->nome ?? '') }}" required>
                            <datalist id="lista-empresas"></datalist>
                            <input type="hidden" id="empresa_id" name="empresa_id"
                                   value="{{ old('empresa_id', $funcionario->empresa_id) }}">
                            <small class="text-muted">Digite para buscar e selecione uma empresa da lista.</small>
                            @error('empresa_nome') <small class="text-danger d-block">{{ $message }}</small> @enderror
                        </div>

                        {{-- Status --}}
                        <div class="form-group">
                            <label><strong>Status</strong></label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo"
                                       {{ old('ativo', $funcionario->ativo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload de novos documentos -->
                <div class="form-group mt-4">
                    <label for="documentos"><strong>Adicionar / Substituir Documentos (PDF/JPG/PNG)</strong></label>
                    <input type="file" id="documentos" name="documentos[]" class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png" multiple>
                    <small class="text-muted d-block mt-1">
                        Você pode adicionar novos arquivos ou substituir documentos existentes. Máx. 5MB por arquivo.
                    </small>
                    <ul id="preview-list" class="list-group mt-2"></ul>
                </div>

                <!-- Botões de ação -->
                <div class="form-group text-center mt-4">
                    <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form> <!-- ✅ FECHAMOS O FORM PRINCIPAL AQUI -->

            <!-- =========================== -->
            <!-- Documentos Existentes -->
            <!-- =========================== -->
            <div class="form-group mt-5">
                <h5>Documentos Existentes:</h5>
                <ul class="list-group">
                    @forelse ($funcionario->documentos as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ asset('storage/' . $doc->caminho_arquivo) }}" target="_blank">
                                    {{ $doc->nome_arquivo }}
                                </a>
                            </div>

                            <div class="d-flex gap-2">
                                <!-- Substituir -->
                                <label class="btn btn-sm btn-outline-primary mb-0">
                                    Substituir
                                    <input type="file" name="replace_documentos[{{ $doc->id }}]"
                                           class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                </label>

                                <!-- Excluir -->
                                <form action="{{ route('funcionarios.documentos.destroy', [$funcionario->id, $doc->id]) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Remover este documento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Nenhum documento enviado.</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</div>

{{-- Scripts: máscara CPF + preview dos arquivos + autocomplete de empresa --}}
<script src="https://cdn.jsdelivr.net/npm/inputmask/dist/inputmask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Máscara CPF
    const imCPF = new Inputmask('999.999.999-99');
    imCPF.mask(document.getElementById('cpf'));

    // Preview dos novos arquivos
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

    // Autocomplete de empresas
    const input = document.getElementById('empresa_nome');
    const hidden = document.getElementById('empresa_id');
    const datalist = document.getElementById('lista-empresas');
    let cache = [];

    async function buscarEmpresas(q) {
        const url = '{{ route('empresas.search') }}?q=' + encodeURIComponent(q);
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
});
</script>
@endsection
