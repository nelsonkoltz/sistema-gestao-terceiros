@extends('layouts.app')

<head>
    <link href="{{ asset('css/usuarios/create.css') }}" rel="stylesheet">
</head>

@section('content')
<div class="container form-container">
    <h1 class="text-center">Cadastrar Novo Usuário</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name">Nome Completo</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="setor">Setor</label>
                <input type="text" name="setor" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="username">Usuário</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email">E-mail (opcional)</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label for="password">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="password_confirmation">Confirmar Senha</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="permissao">Permissão</label>
                <select name="permissao" class="form-control" required>
                    <option value="Usuário">Usuário</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Consulta">Consulta</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-primary">Salvar</button>
    </form>
</div>
@endsection
