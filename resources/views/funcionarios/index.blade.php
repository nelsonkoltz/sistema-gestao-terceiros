@extends('layouts.app')
@section('title', 'Funcionários')

@push('styles')
    <link href="{{ asset('css/funcionarios/index.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">

    <!-- Barra de pesquisa + Novo Funcionário -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <form method="GET"
              action="{{ route('funcionarios.index') }}"
              class="d-flex flex-grow-1 align-items-center search-container">

            <input type="text"
                   name="search"
                   class="search-input"
                   value="{{ request('search') }}"
                   placeholder="Pesquisar por nome, CPF ou empresa">

            <!-- Pesquisar -->
            <button type="submit" class="search-btn" title="Pesquisar">
                <i class="bi bi-search"></i>
            </button>

            <!-- Limpar -->
            <a href="{{ route('funcionarios.index') }}" class="clear-btn" title="Limpar pesquisa">
                <i class="bi bi-x-circle"></i>
            </a>

            <!-- Novo Funcionário -->
            @if(auth()->user()->permissao !== 'Consulta')
<a href="{{ route('funcionarios.create') }}" class="btn-novo" title="Novo funcionário">
                <i class="bi bi-plus-circle"></i>
                Novo Funcionário
            </a>
@endif
        </form>
    </div>

    <!-- Mensagem de sucesso -->
    @if (session('success'))
        <div class="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabela -->
    <div class="table-wrapper">
        <table>

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

                        <td>
                            {{ method_exists($f, 'getCpfFormatadoAttribute') ? $f->cpf_formatado : $f->cpf }}
                        </td>

                        <td>{{ $f->empresa->nome ?? '—' }}</td>

                        <!-- STATUS -->
                        <td>
                            @if($f->ativo)
                                <span class="status status-finalizado">
                                    <i class="bi bi-check-circle"></i> Ativo
                                </span>
                            @else
                                <span class="status status-cancelado">
                                    <i class="bi bi-x-circle"></i> Inativo
                                </span>
                            @endif
                        </td>

                        <!-- AÇÕES -->
                        <td>
                            <div class="actions">

                                <a href="{{ route('funcionarios.show', $f->id) }}"
                                   class="btn btn-view"
                                   title="Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if(auth()->user()->permissao !== 'Consulta')
<a href="{{ route('funcionarios.edit', $f->id) }}"
                                   class="btn btn-edit"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
@endif

                                @if(auth()->user()->permissao !== 'Consulta')
<form action="{{ route('funcionarios.destroy', $f->id) }}"
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
@endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Nenhum funcionário cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- Paginação -->
    <div class="custom-pagination">
        {{ $funcionarios->links() }}
    </div>

</div>
@endsection
