@extends('layouts.app')

@section('title', 'Cadastrar Empresa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/empresas/create.css') }}?v={{ filemtime(public_path('css/empresas/create.css')) }}">
@endpush

@section('content')
<div class="form-container">
    <header class="form-header">
        <div class="form-heading">
            <span class="form-icon" aria-hidden="true"><i class="bi bi-building-add"></i></span>
            <div>
                <h1 class="form-title">Cadastrar empresa</h1>
                <p class="form-subtitle">Registre uma empresa terceirizada ou um prestador pessoa física.</p>
            </div>
        </div>
    </header>

    @if ($errors->any())
        <div class="alert-error" role="alert" aria-live="polite">
            <strong>Revise os campos destacados:</strong>
            <ul>
                @foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('empresas.store') }}" method="POST" enctype="multipart/form-data" id="empresa-form">
        @csrf

        <div class="form-grid">
            <div class="section-heading full">
                <span>Identificação</span>
                <small>Os campos com * são obrigatórios.</small>
            </div>

            <div class="form-group name-field">
                <label for="nome">Nome / Razão social <span>*</span></label>
                <input type="text" id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome') }}" maxlength="255" autocomplete="organization" autofocus required>
                @error('nome')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de cadastro <span>*</span></label>
                <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                    <option value="CNPJ" {{ old('tipo', 'CNPJ') === 'CNPJ' ? 'selected' : '' }}>Pessoa jurídica (CNPJ)</option>
                    <option value="CPF" {{ old('tipo') === 'CPF' ? 'selected' : '' }}>Pessoa física (CPF)</option>
                </select>
                @error('tipo')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="cnpj" id="documento-label">CNPJ <span>*</span></label>
                <input type="text" id="cnpj" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
                       value="{{ old('cnpj') }}" inputmode="numeric" autocomplete="off" required>
                @error('cnpj')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="section-heading full section-spaced"><span>Contato</span></div>

            <div class="form-group">
                <label for="telefone">Telefone <span>*</span></label>
                <input type="tel" id="telefone" name="telefone" class="form-control @error('telefone') is-invalid @enderror"
                       value="{{ old('telefone') }}" inputmode="tel" maxlength="15" autocomplete="tel"
                       placeholder="(00) 00000-0000" required>
                @error('telefone')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group email-field">
                <label for="email">E-mail <span>*</span></label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" maxlength="255" autocomplete="email"
                       placeholder="contato@empresa.com.br" required>
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="section-heading full section-spaced"><span>Endereço</span></div>

            <div class="form-group street-field">
                <label for="endereco_rua">Rua <span>*</span></label>
                <input type="text" id="endereco_rua" name="endereco_rua"
                       class="form-control @error('endereco_rua') is-invalid @enderror"
                       value="{{ old('endereco_rua') }}" maxlength="255" autocomplete="address-line1" required>
                @error('endereco_rua')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group number-field">
                <label for="endereco_numero">Número <span>*</span></label>
                <input type="text" id="endereco_numero" name="endereco_numero"
                       class="form-control @error('endereco_numero') is-invalid @enderror"
                       value="{{ old('endereco_numero') }}" maxlength="20" autocomplete="address-line2" required>
                @error('endereco_numero')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="endereco_bairro">Bairro <span>*</span></label>
                <input type="text" id="endereco_bairro" name="endereco_bairro"
                       class="form-control @error('endereco_bairro') is-invalid @enderror"
                       value="{{ old('endereco_bairro') }}" maxlength="255" required>
                @error('endereco_bairro')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group city-field">
                <label for="endereco_cidade">Cidade <span>*</span></label>
                <input type="text" id="endereco_cidade" name="endereco_cidade"
                       class="form-control @error('endereco_cidade') is-invalid @enderror"
                       value="{{ old('endereco_cidade') }}" maxlength="255" autocomplete="address-level2" required>
                @error('endereco_cidade')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group state-field">
                <label for="endereco_estado">Estado <span>*</span></label>
                <input type="text" id="endereco_estado" name="endereco_estado"
                       class="form-control @error('endereco_estado') is-invalid @enderror"
                       value="{{ old('endereco_estado') }}" maxlength="2" autocomplete="address-level1"
                       placeholder="SC" required>
                @error('endereco_estado')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="form-group cep-field">
                <label for="endereco_cep">CEP <span>*</span></label>
                <input type="text" id="endereco_cep" name="endereco_cep"
                       class="form-control @error('endereco_cep') is-invalid @enderror"
                       value="{{ old('endereco_cep') }}" inputmode="numeric" maxlength="9"
                       autocomplete="postal-code" placeholder="00000-000" required>
                @error('endereco_cep')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="section-heading full section-spaced">
                <span>Situação da empresa</span>
                <small>Empresas inativas não liberam a entrada de nenhum funcionário.</small>
            </div>

            <div class="form-group">
                <label for="ativo">Status <span>*</span></label>
                <select name="ativo" id="ativo" class="form-control" required>
                    <option value="1" {{ old('ativo', '1') === '1' ? 'selected' : '' }}>Ativa</option>
                    <option value="0" {{ old('ativo') === '0' ? 'selected' : '' }}>Inativa</option>
                </select>
            </div>

            <div class="form-group full" id="motivo-inativacao-grupo">
                <label for="motivo_inativacao">Motivo da inativação <span>*</span></label>
                <textarea name="motivo_inativacao" id="motivo_inativacao" class="form-control" rows="3" maxlength="1000" placeholder="Explique por que os acessos desta empresa devem permanecer bloqueados">{{ old('motivo_inativacao') }}</textarea>
                @error('motivo_inativacao')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="section-heading full section-spaced">
                <span>Documentos</span>
                <small>Opcional — outros documentos poderão ser adicionados depois.</small>
            </div>

            <div class="form-group full">
                <label for="documentos" class="upload-area" id="upload-area">
                    <span class="upload-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
                    <strong>Selecione os documentos</strong>
                    <span>ou arraste os arquivos para esta área</span>
                    <small>PDF, JPG ou PNG • até 20 MB por arquivo • máximo de 10 arquivos</small>
                </label>
                <input type="file" id="documentos" name="documentos[]" class="file-input"
                       accept=".pdf,.jpg,.jpeg,.png" multiple>
                @error('documentos')<small class="field-error">{{ $message }}</small>@enderror
                @error('documentos.*')<small class="field-error">{{ $message }}</small>@enderror

                <div id="arquivos-selecionados" class="selected-files" hidden>
                    <div class="selected-files-header">
                        <strong id="resumo-arquivos"></strong>
                        <button type="button" class="clear-files" id="limpar-arquivos">Remover todos</button>
                    </div>
                    <ul id="lista-documentos" class="file-list" aria-live="polite"></ul>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('empresas.index') }}" class="btn btn-cancelar">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button type="submit" class="btn btn-salvar">
                <i class="bi bi-check-lg"></i> Cadastrar empresa
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusEmpresa = document.getElementById('ativo');
    const motivoGrupo = document.getElementById('motivo-inativacao-grupo');
    const motivo = document.getElementById('motivo_inativacao');
    const atualizarStatus = () => {
        const inativa = statusEmpresa.value === '0';
        motivoGrupo.hidden = !inativa;
        motivo.required = inativa;
    };
    statusEmpresa.addEventListener('change', atualizarStatus);
    atualizarStatus();

    const tipo = document.getElementById('tipo');
    const documento = document.getElementById('cnpj');
    const documentoLabel = document.getElementById('documento-label');
    const telefone = document.getElementById('telefone');
    const cep = document.getElementById('endereco_cep');
    const estado = document.getElementById('endereco_estado');

    const digitos = (value, limit) => value.replace(/\D/g, '').slice(0, limit);
    function formatarDocumento() {
        const isCpf = tipo.value === 'CPF';
        let value = digitos(documento.value, isCpf ? 11 : 14);
        documentoLabel.innerHTML = `${isCpf ? 'CPF' : 'CNPJ'} <span>*</span>`;
        documento.placeholder = isCpf ? '000.000.000-00' : '00.000.000/0000-00';
        documento.maxLength = isCpf ? 14 : 18;
        documento.value = isCpf
            ? value.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            : value.replace(/(\d{2})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1/$2').replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    }
    tipo.addEventListener('change', () => { documento.value = ''; formatarDocumento(); documento.focus(); });
    documento.addEventListener('input', formatarDocumento);
    formatarDocumento();

    telefone.addEventListener('input', () => {
        const value = digitos(telefone.value, 11);
        telefone.value = value.length <= 10
            ? value.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
            : value.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
    });
    cep.addEventListener('input', () => cep.value = digitos(cep.value, 8).replace(/(\d{5})(\d)/, '$1-$2'));
    estado.addEventListener('input', () => estado.value = estado.value.replace(/[^a-z]/gi, '').slice(0, 2).toUpperCase());
    telefone.dispatchEvent(new Event('input'));
    cep.dispatchEvent(new Event('input'));

    const input = document.getElementById('documentos');
    const area = document.getElementById('upload-area');
    const box = document.getElementById('arquivos-selecionados');
    const lista = document.getElementById('lista-documentos');
    const resumo = document.getElementById('resumo-arquivos');
    let arquivos = [];
    const chave = file => `${file.name}-${file.size}-${file.lastModified}`;
    const tamanho = bytes => bytes < 1024 * 1024
        ? `${Math.max(1, Math.round(bytes / 1024))} KB`
        : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;

    function sincronizar() {
        const transfer = new DataTransfer();
        arquivos.forEach(file => transfer.items.add(file));
        input.files = transfer.files;
    }

    function renderizar() {
        lista.replaceChildren(...arquivos.map((file, index) => {
            const item = document.createElement('li');
            item.className = 'file-item';
            const icon = document.createElement('i');
            icon.className = file.type === 'application/pdf' ? 'bi bi-file-earmark-pdf' : 'bi bi-file-earmark-image';
            const info = document.createElement('span');
            const name = document.createElement('strong');
            name.textContent = file.name;
            const size = document.createElement('small');
            size.textContent = tamanho(file.size);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'file-remove';
            remove.dataset.index = index;
            remove.setAttribute('aria-label', `Remover ${file.name}`);
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            info.append(name, size);
            item.append(icon, info, remove);
            return item;
        }));
        box.hidden = arquivos.length === 0;
        resumo.textContent = `${arquivos.length} ${arquivos.length === 1 ? 'arquivo selecionado' : 'arquivos selecionados'}`;
        sincronizar();
    }

    function adicionar(files) {
        const existentes = new Set(arquivos.map(chave));
        [...files].forEach(file => { if (!existentes.has(chave(file)) && arquivos.length < 10) arquivos.push(file); });
        renderizar();
    }

    input.addEventListener('change', () => adicionar(input.files));
    lista.addEventListener('click', event => {
        const button = event.target.closest('.file-remove');
        if (!button) return;
        arquivos.splice(Number(button.dataset.index), 1);
        renderizar();
    });
    document.getElementById('limpar-arquivos').addEventListener('click', () => { arquivos = []; renderizar(); });
    ['dragenter', 'dragover'].forEach(name => area.addEventListener(name, event => {
        event.preventDefault(); area.classList.add('is-dragging');
    }));
    area.addEventListener('dragleave', () => area.classList.remove('is-dragging'));
    area.addEventListener('drop', event => {
        event.preventDefault(); area.classList.remove('is-dragging'); adicionar(event.dataTransfer.files);
    });
});
</script>
@endsection
