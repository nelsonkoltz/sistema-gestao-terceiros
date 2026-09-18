@extends('layouts.app')
@section('title','Empresas')
@push('styles')<link href="{{ asset('css/listing.css') }}?v={{ filemtime(public_path('css/listing.css')) }}" rel="stylesheet">@endpush
@section('content')
<div class="listing-page">
 <header class="listing-header"><div><span class="eyebrow">Cadastros</span><h1>Empresas terceirizadas</h1><p>Consulte prestadores, dados de contato e documentos cadastrados.</p></div>@if(auth()->user()->permissao !== 'Consulta')<a href="{{ route('empresas.create') }}" class="primary-action"><i class="bi bi-plus-circle"></i> Nova empresa</a>@endif</header>
 @if(session('success'))<div class="flash success"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>@endif
 <section class="listing-card">
  <div class="listing-toolbar"><form method="GET" action="{{ route('empresas.index') }}" class="search-form"><i class="bi bi-search"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, CPF ou CNPJ" aria-label="Buscar empresas">@if(request('search'))<a href="{{ route('empresas.index') }}" title="Limpar"><i class="bi bi-x-circle"></i></a>@endif<button>Buscar</button></form><span class="result-count">{{ $empresas->total() }} {{ $empresas->total()===1?'empresa':'empresas' }}</span></div>
  @if($empresas->count())
   <div class="table-wrapper"><table><thead><tr><th>Empresa</th><th>CPF / CNPJ</th><th>Telefone</th><th>E-mail</th><th>Status</th><th><span class="sr-only">Ações</span></th></tr></thead><tbody>
   @foreach($empresas as $empresa)<tr><td class="primary-cell"><a href="{{ route('empresas.show',$empresa) }}">{{ $empresa->nome }}</a></td><td class="nowrap">{{ $empresa->cnpj }}</td><td class="nowrap">{{ $empresa->telefone }}</td><td>{{ $empresa->email }}</td><td><span class="status {{ $empresa->ativo?'status-finalizado':'status-cancelado' }}">{{ $empresa->ativo?'Ativa':'Inativa' }}</span></td><td><div class="actions"><a href="{{ route('empresas.show',$empresa) }}" class="icon-btn view" title="Detalhes"><i class="bi bi-eye"></i></a>@if(auth()->user()->permissao !== 'Consulta')<a href="{{ route('empresas.edit',$empresa) }}" class="icon-btn edit" title="Editar"><i class="bi bi-pencil"></i></a><form action="{{ route('empresas.destroy',$empresa) }}" method="POST" onsubmit="return confirm('Deseja excluir esta empresa e seus registros vinculados?')">@csrf @method('DELETE')<button class="icon-btn delete" title="Excluir"><i class="bi bi-trash"></i></button></form>@endif</div></td></tr>@endforeach
   </tbody></table></div><div class="pagination-wrapper">{{ $empresas->links() }}</div>
  @else<div class="empty-state"><span class="empty-icon"><i class="bi bi-buildings"></i></span><h2>{{ request('search')?'Nenhuma empresa encontrada':'Nenhuma empresa cadastrada' }}</h2><p>{{ request('search')?'Tente buscar usando outro nome ou documento.':'Cadastre a primeira empresa terceirizada para vincular funcionários e serviços.' }}</p>@if(request('search'))<a href="{{ route('empresas.index') }}" class="secondary-action">Limpar pesquisa</a>@elseif(auth()->user()->permissao !== 'Consulta')<a href="{{ route('empresas.create') }}" class="primary-action"><i class="bi bi-plus-circle"></i> Cadastrar empresa</a>@endif</div>@endif
 </section>
</div>
@endsection
