@extends('layouts.app')
@section('title', 'Usuários')

@push('styles')
    <link href="{{ asset('css/usuarios/index.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">

    <!-- Barra de pesquisa + Novo Usuário -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <form method="GET"
              action="{{ route('usuarios.index') }}"
              class="d-flex flex-grow-1 align-items-center search-container">

            <input type="text"
                   name="search"
                   class="search-input"
                   value="{{ request('search') }}"
                   placeholder="Pesquisar por nome, usuário ou setor">

            <!-- Pesquisar -->
            <button type="submit" class="search-btn" title="Pesquisar">
                <i class="bi bi-search"></i>
            </button>

            <!-- Limpar -->
            <a href="{{ route('usuarios.index') }}" class="clear-btn" title="Limpar pesquisa">
                <i class="bi bi-x-circle"></i>
            </a>

            <!-- Novo Usuário -->
            <a href="{{ route('usuarios.create') }}" class="btn-novo" title="Novo usuário">
                <i class="bi bi-plus-circle"></i>
                Novo Usuário
            </a>
        </form>
    </div>

    <!-- Mensagens -->
    @if (session('success'))
        <div class="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <i class="bi bi-x-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabela -->
    <div class="table-wrapper">
        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Setor</th>
                    <th>Usuário</th>
                    <th>Permissão</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->id }}</td>
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->setor }}</td>
                        <td>{{ $usuario->username }}</td>

                        <!-- Permissão -->
                        <td>
                            <span class="status status-{{ strtolower($usuario->permissao) }}">
                                <i class="bi bi-shield-lock"></i>
                                {{ ucfirst($usuario->permissao) }}
                            </span>
                        </td>

                        <!-- Ações -->
                        <td>
                            <div class="actions">

                                <a href="{{ route('usuarios.show', $usuario->id) }}"
                                   class="btn btn-view"
                                   title="Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                   class="btn btn-edit"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('usuarios.destroy', $usuario->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-delete"
                                            title="Excluir"
                                            onclick="return confirm('Essa ação não poderá ser desfeita. Deseja continuar?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            Nenhum usuário cadastrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- Paginação -->
    <div class="custom-pagination">
        {{ $usuarios->links() }}
    </div>

</div>
@endsection
