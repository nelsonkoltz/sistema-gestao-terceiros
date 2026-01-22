@extends('layouts.app')

<head>
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">
</head>

@section('content')
    <div class="container">

        <!-- Barra de pesquisa e botão Nova Empresa -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Formulário de pesquisa -->
            <form method="GET" action="{{ route('empresas.index') }}"
                class="d-flex flex-grow-1 align-items-center search-container">
                <input type="text" name="search" class="form-control search-input" value="{{ request('search') }}"
                    placeholder="Pesquisar por Nome ou CNPJ">
                <button type="submit" class="btn search-btn">Pesquisar</button>
                <a href="{{ route('empresas.index') }}" class="btn clear-btn">Limpar</a>

                <div class="NovaEmpresa">
                    <!-- Botão para nova empresa -->
                    <a href="{{ route('empresas.create') }}" class="btn btn-novo">+ Nova Empresa</a>
                </div>
            </form>
        </div>

        <!-- Mensagem de sucesso -->
        @if (session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabela -->
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($empresas as $empresa)
                    <tr>
                        <td>{{ $empresa->nome }}</td>
                        <td>{{ $empresa->cnpj }}</td>
                        <td>{{ $empresa->telefone }}</td>
                        <td>{{ $empresa->email }}</td>
                        <td>
                            <a href="{{ route('empresas.show', $empresa->id) }}" class="btn btn-info">Detalhes</a>
                            <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-warning">Editar</a>
                            <form action="{{ route('empresas.destroy', $empresa->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir esta empresa?')">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-table">
                        <td colspan="5">Nenhuma empresa cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginação -->
        @if ($empresas->hasPages())
            <div class="custom-pagination">
                @if ($empresas->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $empresas->previousPageUrl() }}">&laquo;</a>
                @endif

                @for ($page = 1; $page <= $empresas->lastPage(); $page++)
                    @if ($page == $empresas->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $empresas->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if ($empresas->hasMorePages())
                    <a href="{{ $empresas->nextPageUrl() }}">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </div>
        @endif
    </div>
@endsection