@extends('layouts.app')

<head>
    <link href="{{ asset('css/usuarios/show.css') }}" rel="stylesheet">
</head>

@section('content')
<div class="container">
    <h1 class="text-center">Detalhes do Usuário</h1>

    <div class="card p-4 mt-4">
        <p><strong>Nome:</strong> {{ $usuario->name }}</p>
        <p><strong>Setor:</strong> {{ $usuario->setor }}</p>
        <p><strong>Usuário:</strong> {{ $usuario->username }}</p>
        <p><strong>Email:</strong> {{ $usuario->email ?? '-' }}</p>
        <p><strong>Permissão:</strong> {{ $usuario->permissao }}</p>
        <p><strong>Criado em:</strong> {{ $usuario->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
