@extends('layouts.app')
@section('title', 'Serviços')

<head>
    <link href="{{ asset('css/servicos/index.css') }}" rel="stylesheet">
</head>

@section('content')
    <div class="container">

        <!-- Barra de pesquisa e botão Novo Serviço -->
        <div class="d-flex justify-content-between align-items-center mb-3">

            <form method="GET" action="{{ route('servicos.index') }}"
                class="d-flex flex-grow-1 align-items-center search-container">

                <input type="text" name="search" class="search-input" value="{{ request('search') }}"
                    placeholder="Pesquisar por descrição, empresa ou setor">

                <!-- Pesquisar -->
                <button type="submit" class="search-btn" title="Pesquisar">
                    <i class="bi bi-search"></i>
                </button>

                <!-- Limpar -->
                <a href="{{ route('servicos.index') }}" class="clear-btn" title="Limpar pesquisa">
                    <i class="bi bi-x-circle"></i>
                </a>

                <!-- Novo Serviço -->
                <a href="{{ route('servicos.create') }}" class="btn-novo" title="Novo serviço">
                    <i class="bi bi-plus-circle"></i>
                    Novo Serviço
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
                        <th>Descrição</th>
                        <th>Empresa</th>
                        <th>Setor</th>
                        <th>Solicitante</th>
                        <th>Data</th>
                        <th>Almoço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($servicos as $s)
                        <tr>
                            <td>{{ $s->descricao }}</td>
                            <td>{{ $s->empresa->nome ?? '—' }}</td>
                            <td>{{ $s->setor->nome ?? '—' }}</td>
                            <td>{{ $s->solicitante->name ?? '—' }}</td>
                            <td>{{ $s->data_servico ? \Carbon\Carbon::parse($s->data_servico)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $s->vai_almocar ? 'Sim' : 'Não' }}</td>

                            <!-- STATUS -->
                            <td>
                                @if($s->status === 'Finalizado')
                                    <span class="status status-finalizado">
                                        <i class="bi bi-check-circle"></i> Finalizado
                                    </span>
                                @elseif($s->status === 'Em Andamento')
                                    <span class="status status-andamento">
                                        <i class="bi bi-hourglass-split"></i> Em andamento
                                    </span>
                                @elseif($s->status === 'Cancelado')
                                    <span class="status status-cancelado">
                                        <i class="bi bi-x-circle"></i> Cancelado
                                    </span>
                                @else
                                    <span class="status status-pendente">
                                        <i class="bi bi-clock"></i> Pendente
                                    </span>
                                @endif
                            </td>

                            <!-- AÇÕES -->
                            <td>
                                <div class="actions">
                                    <a href="{{ route('servicos.show', $s->id) }}" class="btn btn-view" title="Detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('servicos.edit', $s->id) }}" class="btn btn-edit" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route('servicos.destroy', $s->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-delete" title="Excluir"
                                            onclick="return confirm('Essa ação não poderá ser desfeita. Deseja continuar?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Nenhum serviço cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Paginação -->
        <div class="custom-pagination">
            {{ $servicos->links() }}
        </div>

    </div>
@endsection