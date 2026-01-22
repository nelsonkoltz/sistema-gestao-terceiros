@extends('layouts.app')
@section('title', 'Funcionários')

<head>
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">
</head>

@section('content')
    <div class="container">
        <!-- Barra de pesquisa e botão Novo Funcionário -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Formulário de pesquisa -->
            <form method="GET" action="{{ route('funcionarios.index') }}"
                class="d-flex flex-grow-1 align-items-center search-container">

                <!-- Campo de pesquisa -->
                <input type="text" name="search" class="form-control search-input flex-grow-1"
                    value="{{ request('search') }}" placeholder="Pesquisar por Nome, CPF ou Empresa">

                <!-- Botão Pesquisar -->
                <button type="submit" class="btn btn-primary search-btn">Pesquisar</button>

                <!-- Botão Limpar -->
                <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary clear-btn">Limpar</a>


            <!-- Botão Novo Funcionário -->
            <a href="{{ route('funcionarios.create') }}" class="btn btn-novo">Novo Funcionário</a>
                        </form>
        </div>


        <!-- Mensagem de sucesso -->
        @if (session('success'))
            <div class="alert mt-2">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabela para exibir funcionários -->
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Empresa</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funcionarios as $f)
                    <tr>
                        <td>{{ $f->nome }}</td>
                        <td>{{ method_exists($f, 'getCpfFormatadoAttribute') ? $f->cpf_formatado : $f->cpf }}</td>
                        <td>{{ $f->empresa->nome ?? '—' }}</td>
                        <td>
                            @if($f->ativo)
                                <span class="status-badge status-ativo">Ativo</span>
                            @else
                                <span class="status-badge status-inativo">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('funcionarios.show', $f->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            <a href="{{ route('funcionarios.edit', $f->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('funcionarios.destroy', $f->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-table">
                        <td colspan="5">Nenhum funcionário cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginação -->
        <div class="custom-pagination">
            @if ($funcionarios->onFirstPage())
                <span class="disabled">&laquo;</span>
            @else
                <a href="{{ $funcionarios->previousPageUrl() }}">&laquo;</a>
            @endif

            @for ($page = 1; $page <= $funcionarios->lastPage(); $page++)
                @if ($page == $funcionarios->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $funcionarios->url($page) }}">{{ $page }}</a>
                @endif
            @endfor

            @if ($funcionarios->hasMorePages())
                <a href="{{ $funcionarios->nextPageUrl() }}">&raquo;</a>
            @else
                <span class="disabled">&raquo;</span>
            @endif
        </div>
    </div>
@endsection