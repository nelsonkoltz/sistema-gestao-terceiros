@extends('layouts.app')
@section('title','Funcionários')
@push('styles')<link href="{{ asset('css/listing.css') }}?v={{ filemtime(public_path('css/listing.css')) }}" rel="stylesheet">@endpush
@section('content')
<div class="listing-page">
 <header class="listing-header"><div><span class="eyebrow">Cadastros</span><h1>Funcionários terceirizados</h1><p>Consulte pessoas, empresas vinculadas e situação do cadastro.</p></div>@if(auth()->user()->permissao !== 'Consulta')<a href="{{ route('funcionarios.create') }}" class="primary-action"><i class="bi bi-person-plus"></i> Novo funcionário</a>@endif</header>
 @if(session('success'))<div class="flash success"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>@endif
 <section class="listing-card">
  <div class="listing-toolbar"><form method="GET" action="{{ route('funcionarios.index') }}" class="search-form"><i class="bi bi-search"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, CPF ou empresa" aria-label="Buscar funcionários">@if(request('search'))<a href="{{ route('funcionarios.index') }}" title="Limpar"><i class="bi bi-x-circle"></i></a>@endif<button>Buscar</button></form><span class="result-count">{{ $funcionarios->total() }} {{ $funcionarios->total()===1?'funcionário':'funcionários' }}</span></div>
  @if($funcionarios->count())
   <div class="table-wrapper"><table><thead><tr><th>Funcionário</th><th>CPF</th><th>Empresa</th><th>Status</th><th><span class="sr-only">Ações</span></th></tr></thead><tbody>
   @foreach($funcionarios as $f)<tr><td class="primary-cell"><a href="{{ route('funcionarios.show',$f) }}">{{ $f->nome }}</a></td><td class="nowrap">{{ $f->cpf_formatado }}</td><td>{{ $f->empresa->nome ?? '—' }}</td><td><span class="status {{ $f->ativo?'status-finalizado':'status-cancelado' }}">{{ $f->ativo?'Ativo':'Inativo' }}</span></td><td><div class="actions"><a href="{{ route('funcionarios.show',$f) }}" class="icon-btn view" title="Detalhes"><i class="bi bi-eye"></i></a>@if(auth()->user()->permissao !== 'Consulta')<a href="{{ route('funcionarios.edit',$f) }}" class="icon-btn edit" title="Editar"><i class="bi bi-pencil"></i></a><form action="{{ route('funcionarios.destroy',$f) }}" method="POST" onsubmit="return confirm('Deseja excluir este funcionário?')">@csrf @method('DELETE')<button class="icon-btn delete" title="Excluir"><i class="bi bi-trash"></i></button></form>@endif</div></td></tr>@endforeach
   </tbody></table></div><div class="pagination-wrapper">{{ $funcionarios->links() }}</div>
  @else<div class="empty-state"><span class="empty-icon"><i class="bi bi-person-badge"></i></span><h2>{{ request('search')?'Nenhum funcionário encontrado':'Nenhum funcionário cadastrado' }}</h2><p>{{ request('search')?'Tente buscar usando outro nome, CPF ou empresa.':'Cadastre funcionários terceirizados para consultar documentos e vinculá-los aos serviços.' }}</p>@if(request('search'))<a href="{{ route('funcionarios.index') }}" class="secondary-action">Limpar pesquisa</a>@elseif(auth()->user()->permissao !== 'Consulta')<a href="{{ route('funcionarios.create') }}" class="primary-action"><i class="bi bi-person-plus"></i> Cadastrar funcionário</a>@endif</div>@endif
 </section>
</div>
@endsection
