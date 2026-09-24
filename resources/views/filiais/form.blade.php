@extends('layouts.app')
@section('title',$filial->exists?'Editar filial':'Cadastrar filial')
@push('styles')<link rel="stylesheet" href="{{ asset('css/branches.css') }}?v={{ filemtime(public_path('css/branches.css')) }}">@endpush
@section('content')
<div class="branch-form-page">
 <nav class="branch-breadcrumb"><a href="{{ route('filiais.index') }}">Filiais</a><i class="bi bi-chevron-right"></i><span>{{ $filial->exists?'Editar':'Cadastrar' }}</span></nav>
 <section class="branch-form-card"><header><span class="branch-icon"><i class="bi bi-building-add"></i></span><div><h1>{{ $filial->exists?'Editar filial':'Cadastrar filial' }}</h1><p>Informe a identificação e os dados de contato da unidade.</p></div></header>
 @if($errors->any())<div class="branch-alert danger"><ul>@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
 <form method="POST" action="{{ $filial->exists?route('filiais.update',$filial):route('filiais.store') }}">@csrf @if($filial->exists)@method('PUT')@endif
  <div class="branch-form-grid">
   <label>Nome da filial *<input name="nome" value="{{ old('nome',$filial->nome) }}" required></label>
   <label>Código *<input name="codigo" value="{{ old('codigo',$filial->codigo) }}" required maxlength="30" placeholder="Ex.: MATRIZ"></label>
   <label>CNPJ<input name="cnpj" value="{{ old('cnpj',$filial->cnpj) }}" inputmode="numeric" maxlength="14" placeholder="Somente números"></label>
   <label>Status<select name="ativa" required><option value="1" {{ old('ativa',$filial->exists?$filial->ativa:1)?'selected':'' }}>Ativa</option><option value="0" {{ (string)old('ativa',$filial->ativa?'1':'0')==='0'?'selected':'' }}>Inativa</option></select></label>
   <label>Telefone<input name="telefone" value="{{ old('telefone',$filial->telefone) }}"></label>
   <label>E-mail<input type="email" name="email" value="{{ old('email',$filial->email) }}"></label>
   <label class="full">Endereço<input name="endereco" value="{{ old('endereco',$filial->endereco) }}"></label>
   <label>Cidade<input name="cidade" value="{{ old('cidade',$filial->cidade) }}"></label>
   <label>Estado<input name="estado" value="{{ old('estado',$filial->estado) }}" maxlength="2" placeholder="SC"></label>
  </div>
  <div class="branch-form-actions"><a href="{{ $filial->exists?route('filiais.show',$filial):route('filiais.index') }}">Cancelar</a><button><i class="bi bi-check-lg"></i> Salvar filial</button></div>
 </form></section>
</div>
@endsection
