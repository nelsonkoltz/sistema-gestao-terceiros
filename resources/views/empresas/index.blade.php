@extends('layouts.app')
@section('title', 'Empresas')

@push('styles')
    <link href="{{ asset('css/empresas/index.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">

    <!-- Barra de pesquisa e botão Nova Empresa -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <form method="GET"
              action="{{ route('empresas.index') }}"
              class="d-flex flex-grow-1 align-items-center search-container">

            <input type="text"
                   name="search"
                   class="search-input"
                   value="{{ request('search') }}"
                   placeholder="Pesquisar por nome, CPF ou CNPJ">

            <!-- Pesquisar -->
            <button type="submit" class="search-btn" title="Pesquisar">
                <i class="bi bi-search"></i>
            </button>

            <!-- Limpar -->
            <a href="{{ route('empresas.index') }}" class="clear-btn" title="Limpar pesquisa">
                <i class="bi bi-x-circle"></i>
            </a>

            <!-- Nova Empresa -->
            <a href="{{ route('empresas.create') }}" class="btn-novo" title="Nova empresa">
                <i class="bi bi-plus-circle"></i>
                Nova Empresa
            </a>
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
                    <th>CPF / CNPJ</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
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

                        <!-- AÇÕES -->
                        <td>
                            <div class="actions">

                                <a href="{{ route('empresas.show', $empresa->id) }}"
                                   class="btn btn-view"
                                   title="Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('empresas.edit', $empresa->id) }}"
                                   class="btn btn-edit"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('empresas.destroy', $empresa->id) }}"
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
                        <td colspan="5">Nenhuma empresa cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- Paginação -->
    <div class="custom-pagination">
        {{ $empresas->links() }}
    </div>

</div>
@endsection
