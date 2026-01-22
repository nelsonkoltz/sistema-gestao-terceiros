@extends('layouts.app')

<head>
    <link href="{{ asset('css/usuarios/edit.css') }}" rel="stylesheet">
</head>

@section('content')
<div class="container form-container">
    <h1 class="text-center">Editar Usuário</h1>

    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nome Completo</label>
                <input type="text" name="name" class="form-control" value="{{ $usuario->name }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Setor</label>
                <input type="text" name="setor" class="form-control" value="{{ $usuario->setor }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Usuário</label>
                <input type="text" name="username" class="form-control" value="{{ $usuario->username }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $usuario->email }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Nova Senha (opcional)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label>Permissão</label>
                <select name="permissao" class="form-control">
                    <option value="Usuário" {{ $usuario->permissao == 'Usuário' ? 'selected' : '' }}>Usuário</option>
                    <option value="Administrador" {{ $usuario->permissao == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                    <option value="Consulta" {{ $usuario->permissao == 'Consulta' ? 'selected' : '' }}>Consulta</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-primary">Atualizar</button>
    </form>
</div>
@endsection
