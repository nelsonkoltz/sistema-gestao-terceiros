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
                       value="{{ old('email', $usuario->email) }}"
                       required>
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
                    <option value="Administrador" {{ old('permissao', $usuario->permissao) == 'Administrador' ? 'selected' : '' }}>
                        Administrador
                    </option>
                    <option value="Solicitante" {{ old('permissao', $usuario->permissao) == 'Solicitante' ? 'selected' : '' }}>Solicitante</option>
                    <option value="Segurança do Trabalho" {{ old('permissao', $usuario->permissao) == 'Segurança do Trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                    <option value="Guarita" {{ old('permissao', $usuario->permissao) == 'Guarita' ? 'selected' : '' }}>Guarita</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Status da conta</label>
                <select name="ativo" id="user-active" class="form-control" required {{ auth()->id() === $usuario->id ? 'disabled' : '' }}>
                    <option value="1" {{ old('ativo', $usuario->ativo ? '1' : '0') === '1' ? 'selected' : '' }}>Ativa</option>
                    <option value="0" {{ old('ativo', $usuario->ativo ? '1' : '0') === '0' ? 'selected' : '' }}>Inativa</option>
                </select>
                @if(auth()->id() === $usuario->id)<input type="hidden" name="ativo" value="1"><small>Você não pode inativar a própria conta.</small>@endif
            </div>
            <div class="form-group full" id="inactive-reason" style="display:none">
                <label>Motivo da inativação</label>
                <textarea name="motivo_inativacao" class="form-control" maxlength="1000" placeholder="Informe por que esta conta não deve mais acessar o sistema">{{ old('motivo_inativacao', $usuario->motivo_inativacao) }}</textarea>
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
<script>document.addEventListener('DOMContentLoaded',function(){const status=document.getElementById('user-active');const box=document.getElementById('inactive-reason');if(!status||!box)return;const sync=()=>{const inactive=status.value==='0';box.style.display=inactive?'block':'none';box.querySelector('textarea').required=inactive};status.addEventListener('change',sync);sync()});</script>
@endsection
