@extends('layouts.app')
@section('title', 'Configurações')
@push('styles')<link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ filemtime(public_path('css/settings.css')) }}">@endpush
@section('content')
<div class="settings-page">
 <header class="settings-header"><div><span class="eyebrow">Administração</span><h1>Configurações</h1><p>Controle a rotina automática de validade dos documentos.</p></div></header>
 @if(session('success'))<div class="settings-alert success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="settings-alert danger">{{ $errors->first() }}</div>@endif
 <section class="settings-card job-card">
  <div class="job-copy"><span class="settings-icon"><i class="bi bi-arrow-repeat"></i></span><div><h2>Verificação automática de validade</h2><p>Executada diariamente às 00:10. Todo documento vence seis meses após seu cadastro e deverá ser renovado.</p><span class="job-state {{ $jobAtivo?'on':'off' }}"><i class="bi bi-circle-fill"></i>{{ $jobAtivo?'Job ativo':'Job desativado' }}</span></div></div>
  <div class="job-actions"><form method="POST" action="{{ route('configuracoes.job') }}">@csrf @method('PUT')<input type="hidden" name="ativo" value="{{ $jobAtivo?0:1 }}"><button class="button {{ $jobAtivo?'secondary':'primary' }}" type="submit">{{ $jobAtivo?'Desativar job':'Ativar job' }}</button></form><form method="POST" action="{{ route('configuracoes.job.executar') }}">@csrf<button class="button ghost" type="submit"><i class="bi bi-play-fill"></i> Executar agora</button></form></div>
 </section>
</div>
@endsection
