@extends('layouts.app')
@section('title','Usuários')
@push('styles')<link href="{{ asset('css/listing.css') }}?v={{ filemtime(public_path('css/listing.css')) }}" rel="stylesheet">@endpush
@section('content')
<div class="listing-page">
 <header class="listing-header"><div><span class="eyebrow">Administração</span><h1>Usuários do sistema</h1><p>Gerencie acessos, setores e níveis de permissão.</p></div><a href="{{ route('usuarios.create') }}" class="primary-action"><i class="bi bi-person-plus"></i> Novo usuário</a></header>
 @if(session('success'))<div class="flash success"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>@endif
 @if(session('error'))<div class="flash error"><i class="bi bi-exclamation-circle"></i>{{ session('error') }}</div>@endif
 <section class="listing-card">
  <div class="listing-toolbar"><form method="GET" action="{{ route('usuarios.index') }}" class="search-form"><i class="bi bi-search"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, usuário ou setor" aria-label="Buscar usuários">@if(request('search'))<a href="{{ route('usuarios.index') }}" title="Limpar"><i class="bi bi-x-circle"></i></a>@endif<button>Buscar</button></form><span class="result-count">{{ $usuarios->total() }} {{ $usuarios->total()===1?'usuário':'usuários' }}</span></div>
  @if($usuarios->count())
   <div class="table-wrapper"><table><thead><tr><th>Usuário</th><th>Login</th><th>Setor</th><th>Permissão</th><th><span class="sr-only">Ações</span></th></tr></thead><tbody>
   @foreach($usuarios as $usuario) @php $permissionClass=$usuario->permissao==='Administrador'?'administrator':($usuario->permissao==='Consulta'?'readonly':'operator'); @endphp
    <tr><td class="user-cell"><span class="user-avatar">{{ mb_strtoupper(mb_substr($usuario->name,0,1)) }}</span><span><a href="{{ route('usuarios.show',$usuario) }}">{{ $usuario->name }}</a>@if(auth()->id()===$usuario->id)<small>Você</small>@endif</span></td><td><span class="username">{{ $usuario->username }}</span></td><td>{{ $usuario->setor?:'—' }}</td><td><span class="permission {{ $permissionClass }}"><i class="bi bi-shield-check"></i>{{ $usuario->permissao }}</span></td><td><div class="actions"><a href="{{ route('usuarios.show',$usuario) }}" class="icon-btn view" title="Detalhes"><i class="bi bi-eye"></i></a><a href="{{ route('usuarios.edit',$usuario) }}" class="icon-btn edit" title="Editar"><i class="bi bi-pencil"></i></a>@if(auth()->id()!==$usuario->id)<form action="{{ route('usuarios.destroy',$usuario) }}" method="POST" onsubmit="return confirm('Deseja excluir este usuário?')">@csrf @method('DELETE')<button class="icon-btn delete" title="Excluir"><i class="bi bi-trash"></i></button></form>@endif</div></td></tr>
   @endforeach</tbody></table></div><div class="pagination-wrapper">{{ $usuarios->links() }}</div>
  @else<div class="empty-state"><span class="empty-icon"><i class="bi bi-people"></i></span><h2>{{ request('search')?'Nenhum usuário encontrado':'Nenhum usuário cadastrado' }}</h2><p>{{ request('search')?'Tente buscar usando outro nome, login ou setor.':'Cadastre usuários para permitir o acesso dos setores ao sistema.' }}</p>@if(request('search'))<a href="{{ route('usuarios.index') }}" class="secondary-action">Limpar pesquisa</a>@else<a href="{{ route('usuarios.create') }}" class="primary-action"><i class="bi bi-person-plus"></i> Cadastrar usuário</a>@endif</div>@endif
 </section>
</div>
@endsection
