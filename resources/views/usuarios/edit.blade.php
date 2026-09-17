@extends('layouts.app')

@section('title', 'Editar Usuário')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/edit-form.css') }}?v={{ filemtime(public_path('css/edit-form.css')) }}">
@endpush

@section('content')
<div class="form-container">

    {{-- HEADER --}}
    <header class="form-header">
        <h1 class="form-title">Editar Usuário</h1>
        <p class="form-subtitle">
            Atualize os dados do usuário e suas permissões de acesso.
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
    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grid">

            {{-- NOME --}}
            <div class="form-group">
                <label>Nome</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name', $usuario->name) }}"
                       required>
            </div>

            {{-- SETOR --}}
            <div class="form-group">
                <label>Setor</label>
                <input type="text"
                       name="setor"
                       class="form-control"
                       value="{{ old('setor', $usuario->setor) }}"
                       required>
            </div>

            {{-- USUÁRIO --}}
            <div class="form-group">
                <label>Usuário</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       value="{{ old('username', $usuario->username) }}"
                       required>
            </div>

            {{-- EMAIL --}}
            <div class="form-group">
                <label>E-mail</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', $usuario->email) }}">
            </div>

            {{-- NOVA SENHA --}}
            <div class="form-group">
                <label>Nova Senha (opcional)</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Deixe em branco para manter a atual">
            </div>

            {{-- CONFIRMAR SENHA --}}
            <div class="form-group">
                <label>Confirmar Nova Senha</label>
                <input type="password"
                       name="password_confirmation"
                       class="form-control"
                       placeholder="Confirme a nova senha">
            </div>

            {{-- PERMISSÃO --}}
            <div class="form-group full">
                <label>Permissão</label>
                <select name="permissao" class="form-control" required>
                    <option value="Usuário" {{ old('permissao', $usuario->permissao) == 'Usuário' ? 'selected' : '' }}>
                        Usuário
                    </option>
                    <option value="Administrador" {{ old('permissao', $usuario->permissao) == 'Administrador' ? 'selected' : '' }}>
                        Administrador
                    </option>
                    <option value="Consulta" {{ old('permissao', $usuario->permissao) == 'Consulta' ? 'selected' : '' }}>
                        Consulta
                    </option>
                </select>
            </div>

        </div>

        {{-- AÇÕES --}}
        <div class="form-actions">
            <a href="{{ route('usuarios.show', $usuario) }}" class="btn btn-cancelar">
                Voltar
            </a>

            <button type="submit" class="btn btn-salvar">
                Salvar alterações
            </button>
        </div>

    </form>
</div>
@endsection
