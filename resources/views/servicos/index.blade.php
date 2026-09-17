@extends('layouts.app')
@section('title', 'Solicitações de Serviço')
@push('styles')
<link href="{{ asset('css/listing.css') }}?v={{ filemtime(public_path('css/listing.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="listing-page">
    <header class="listing-header">
        <div><span class="eyebrow">Operação</span><h1>Solicitações de serviço</h1><p>Acompanhe os serviços solicitados às empresas terceirizadas.</p></div>
        @if(auth()->user()->permissao !== 'Consulta')
            <a href="{{ route('servicos.create') }}" class="primary-action"><i class="bi bi-plus-circle"></i> Nova solicitação</a>
        @endif
    </header>

    @if(session('success'))<div class="flash success"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>@endif

    <section class="listing-card">
        <div class="listing-toolbar">
            <form method="GET" action="{{ route('servicos.index') }}" class="search-form">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por descrição, empresa ou setor" aria-label="Buscar solicitações">
                @if(request('search'))<a href="{{ route('servicos.index') }}" title="Limpar pesquisa"><i class="bi bi-x-circle"></i></a>@endif
                <button type="submit">Buscar</button>
            </form>
            <span class="result-count">{{ $servicos->total() }} {{ $servicos->total() === 1 ? 'solicitação' : 'solicitações' }}</span>
        </div>

        @if($servicos->count())
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Serviço</th><th>Empresa</th><th>Setor</th><th>Solicitante</th><th>Data</th><th>Refeitório</th><th>Status</th><th><span class="sr-only">Ações</span></th></tr></thead>
                    <tbody>
                    @foreach($servicos as $s)
                        <tr>
                            <td class="primary-cell"><a href="{{ route('servicos.show', $s) }}">{{ $s->descricao }}</a></td>
                            <td>{{ $s->empresa->nome ?? '—' }}</td><td>{{ $s->setor->nome ?? '—' }}</td><td>{{ $s->solicitante->name ?? '—' }}</td>
                            <td class="nowrap">{{ optional($s->data_servico)->format('d/m/Y') ?? '—' }}</td>
                            <td><span class="yes-no {{ $s->vai_almocar ? 'yes' : 'no' }}">{{ $s->vai_almocar ? 'Sim' : 'Não' }}</span></td>
                            <td><span class="status status-{{ str_replace(' ', '-', mb_strtolower($s->status)) }}">{{ $s->status }}</span></td>
                            <td><div class="actions">
                                <a href="{{ route('servicos.show', $s) }}" class="icon-btn view" title="Ver detalhes"><i class="bi bi-eye"></i></a>
                                @if(auth()->user()->permissao !== 'Consulta')
                                    <a href="{{ route('servicos.edit', $s) }}" class="icon-btn edit" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('servicos.destroy', $s) }}" method="POST" onsubmit="return confirm('Deseja excluir esta solicitação?')">@csrf @method('DELETE')<button class="icon-btn delete" title="Excluir"><i class="bi bi-trash"></i></button></form>
                                @endif
                            </div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">{{ $servicos->links() }}</div>
        @else
            <div class="empty-state"><span class="empty-icon"><i class="bi bi-clipboard2-check"></i></span>
                <h2>{{ request('search') ? 'Nenhuma solicitação encontrada' : 'Nenhuma solicitação cadastrada' }}</h2>
                <p>{{ request('search') ? 'Tente buscar usando outros termos.' : 'Quando um serviço for solicitado, ele aparecerá aqui para acompanhamento.' }}</p>
                @if(request('search'))<a href="{{ route('servicos.index') }}" class="secondary-action">Limpar pesquisa</a>
                @elseif(auth()->user()->permissao !== 'Consulta')<a href="{{ route('servicos.create') }}" class="primary-action"><i class="bi bi-plus-circle"></i> Criar primeira solicitação</a>@endif
            </div>
        @endif
    </section>
</div>
@endsection
