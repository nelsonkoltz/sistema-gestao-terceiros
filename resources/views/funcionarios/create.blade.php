@extends('layouts.app')

@section('title', 'Cadastrar Funcionário')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/funcionarios/create.css') }}?v={{ filemtime(public_path('css/funcionarios/create.css')) }}">
@endpush

@section('content')
<div class="form-container">
    <header class="form-header">
        <div class="form-heading">
            <span class="form-icon" aria-hidden="true"><i class="bi bi-person-plus"></i></span>
            <div>
                <h1 class="form-title">Cadastrar funcionário</h1>
                <p class="form-subtitle">Informe os dados de identificação e, se necessário, anexe os documentos.</p>
            </div>
        </div>
    </header>

    @if ($errors->any())
        <div class="alert-error" role="alert" aria-live="polite">
            <strong>Revise os campos destacados:</strong>
            <ul>
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('funcionarios.store') }}" method="POST" enctype="multipart/form-data" id="funcionario-form">
        @csrf

        <div class="form-grid">
            <div class="section-heading full">
                <span>Dados do funcionário</span>
                <small>Os campos com * são obrigatórios.</small>
            </div>

            <div class="form-group">
                <label for="nome">Nome completo <span aria-hidden="true">*</span></label>
                <input type="text" id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome') }}" maxlength="255" autocomplete="name" autofocus
                       aria-describedby="nome-error" required>
                @error('nome')<small class="field-error" id="nome-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="cpf">CPF <span aria-hidden="true">*</span></label>
                <input type="text" id="cpf" name="cpf" class="form-control @error('cpf') is-invalid @enderror"
                       value="{{ old('cpf') }}" inputmode="numeric" autocomplete="off" maxlength="14"
                       placeholder="000.000.000-00" aria-describedby="cpf-help cpf-error" required>
                <small id="cpf-help">Digite os números do CPF.</small>
                @error('cpf')<small class="field-error" id="cpf-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group full">
                <label for="empresa_id">Empresa <span aria-hidden="true">*</span></label>
                <select id="empresa_id" name="empresa_id"
                        class="form-control @error('empresa_id') is-invalid @enderror"
                        aria-describedby="empresa-help empresa-error" required>
                    <option value="">Selecione a empresa do funcionário</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ (string) old('empresa_id') === (string) $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nome }} — {{ $empresa->cnpj_formatado }}
                        </option>
                    @endforeach
                </select>
                <small id="empresa-help">A empresa precisa estar cadastrada antes de vincular o funcionário.</small>
                @error('empresa_id')<small class="field-error" id="empresa-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group status-field">
                <label for="ativo">Status <span aria-hidden="true">*</span></label>
                <select name="ativo" id="ativo" class="form-control">
                    <option value="1" {{ old('ativo', 1) == 1 ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ old('ativo') == 0 ? 'selected' : '' }}>Inativo</option>
                </select>
                <small>Funcionários inativos não devem ser liberados na guarita.</small>
            </div>

            <div class="section-heading full documents-heading">
                <span>Documentos</span>
                <small>Opcional — outros documentos poderão ser adicionados depois.</small>
            </div>

            <div class="form-group full">
                <label for="documentos" class="upload-area" id="upload-area">
                    <span class="upload-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
                    <strong>Selecione os documentos</strong>
                    <span>ou arraste os arquivos para esta área</span>
                    <small>PDF, JPG ou PNG • até 5 MB por arquivo • máximo de 10 arquivos</small>
                </label>
                <input type="file" id="documentos" name="documentos[]" class="file-input"
                       accept=".pdf,.jpg,.jpeg,.png" multiple>
                @error('documentos')<small class="field-error">{{ $message }}</small>@enderror
                @error('documentos.*')<small class="field-error">{{ $message }}</small>@enderror

                <div id="arquivos-selecionados" class="selected-files" hidden>
                    <div class="selected-files-header">
                        <strong id="resumo-arquivos">Arquivos selecionados</strong>
                        <button type="button" class="clear-files" id="limpar-arquivos">Remover todos</button>
                    </div>
                    <ul id="lista-documentos" class="file-list" aria-live="polite"></ul>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('funcionarios.index') }}" class="btn btn-cancelar">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar
            </a>
            <button type="submit" class="btn btn-salvar">
                <i class="bi bi-check-lg" aria-hidden="true"></i> Cadastrar funcionário
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cpf = document.getElementById('cpf');
    const formatarCpf = value => value.replace(/\D/g, '').slice(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    cpf.value = formatarCpf(cpf.value);
    cpf.addEventListener('input', () => cpf.value = formatarCpf(cpf.value));

    const inputDocs = document.getElementById('documentos');
    const uploadArea = document.getElementById('upload-area');
    const selectedFiles = document.getElementById('arquivos-selecionados');
    const fileList = document.getElementById('lista-documentos');
    const fileSummary = document.getElementById('resumo-arquivos');
    const clearFiles = document.getElementById('limpar-arquivos');
    const formatarTamanho = bytes => bytes < 1024 * 1024
        ? `${Math.max(1, Math.round(bytes / 1024))} KB`
        : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;

    function renderizarArquivos() {
        const files = [...inputDocs.files];
        fileList.replaceChildren(...files.map(file => {
            const item = document.createElement('li');
            item.className = 'file-item';
            const icon = document.createElement('i');
            icon.className = file.type === 'application/pdf' ? 'bi bi-file-earmark-pdf' : 'bi bi-file-earmark-image';
            const info = document.createElement('span');
            const name = document.createElement('strong');
            name.textContent = file.name;
            const size = document.createElement('small');
            size.textContent = formatarTamanho(file.size);
            info.append(name, size);
            item.append(icon, info);
            return item;
        }));
        selectedFiles.hidden = files.length === 0;
        fileSummary.textContent = `${files.length} ${files.length === 1 ? 'arquivo selecionado' : 'arquivos selecionados'}`;
    }

    inputDocs.addEventListener('change', renderizarArquivos);
    clearFiles.addEventListener('click', () => {
        inputDocs.value = '';
        renderizarArquivos();
    });
    ['dragenter', 'dragover'].forEach(name => uploadArea.addEventListener(name, event => {
        event.preventDefault();
        uploadArea.classList.add('is-dragging');
    }));
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('is-dragging'));
    uploadArea.addEventListener('drop', event => {
        event.preventDefault();
        uploadArea.classList.remove('is-dragging');
        inputDocs.files = event.dataTransfer.files;
        renderizarArquivos();
    });

});
</script>
@endsection
