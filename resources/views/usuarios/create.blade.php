@extends('layouts.app')

@section('title', 'Cadastrar Usuário')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/usuarios/create.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Cadastrar Usuário</h1>
        <p class="form-subtitle">
            Preencha os dados abaixo para registrar um novo usuário.
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
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf

        <div class="form-grid">

            {{-- NOME --}}
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       required>
            </div>

            {{-- SETOR --}}
            <div class="form-group">
                <label>Setor</label>
                <input type="text"
                       name="setor"
                       class="form-control"
                       value="{{ old('setor') }}"
                       required>
            </div>

            {{-- USUÁRIO --}}
            <div class="form-group">
                <label>Usuário</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       value="{{ old('username') }}"
                       required>
            </div>

            {{-- E-MAIL --}}
            <div class="form-group">
                <label>E-mail</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       required>
            </div>

            {{-- SENHA --}}
            <div class="form-group">
                <label>Senha</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       required>
            </div>

            {{-- CONFIRMAR SENHA --}}
            <div class="form-group">
                <label>Confirmar Senha</label>
                <input type="password"
                       name="password_confirmation"
                       class="form-control"
                       required>
            </div>

            {{-- PERMISSÃO --}}
            <div class="form-group full">
                <label>Permissão</label>
                <select name="permissao" class="form-control" required>
                    <option value="">Selecione</option>
                    <option value="Administrador" {{ old('permissao') == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                    <option value="Solicitante" {{ old('permissao') == 'Solicitante' ? 'selected' : '' }}>Solicitante</option>
                    <option value="Segurança do Trabalho" {{ old('permissao') == 'Segurança do Trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                    <option value="Guarita" {{ old('permissao') == 'Guarita' ? 'selected' : '' }}>Guarita</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Status da conta</label>
                <select name="ativo" class="form-control" required>
                    <option value="1" {{ old('ativo', '1') === '1' ? 'selected' : '' }}>Ativa</option>
                    <option value="0" {{ old('ativo') === '0' ? 'selected' : '' }}>Inativa</option>
                </select>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn btn-cancelar">
                Voltar
            </a>

            <button type="submit" class="btn btn-salvar">
                Salvar
            </button>
        </div>

    </form>
</div>
@endsection
