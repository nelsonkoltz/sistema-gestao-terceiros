@extends('layouts.app')
@section('title', 'Minha conta')
@push('styles')<link rel="stylesheet" href="{{ asset('css/details.css') }}?v={{ filemtime(public_path('css/details.css')) }}"><link rel="stylesheet" href="{{ asset('css/edit-form.css') }}?v={{ filemtime(public_path('css/edit-form.css')) }}">@endpush
@section('content')
<div class="details-page account-page">
    <header class="detail-header"><div class="identity"><span class="identity-icon"><i class="bi bi-person-lock"></i></span><div><span class="eyebrow">Segurança da conta</span><h1>Minha conta</h1><p>Atualize sua senha de acesso com segurança.</p></div></div></header>
    @if(session('success'))<div class="flash success"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="flash danger"><i class="bi bi-exclamation-triangle"></i>{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="flash danger"><i class="bi bi-exclamation-circle"></i>{{ $errors->first() }}</div>@endif

    <section class="summary-grid"><div class="summary-card"><i class="bi bi-person"></i><span><strong>{{ $usuario->name }}</strong><small>{{ '@'.$usuario->username }}</small></span></div><div class="summary-card"><i class="bi bi-shield-check"></i><span><strong>{{ $usuario->permissao }}</strong><small>Perfil</small></span></div><div class="summary-card"><i class="bi bi-key"></i><span><strong>{{ $usuario->trocar_senha ? 'Troca obrigatória' : 'Senha definida' }}</strong><small>{{ $usuario->senha_alterada_em ? 'Alterada em '.$usuario->senha_alterada_em->format('d/m/Y H:i') : 'Sem alteração registrada' }}</small></span></div></section>

    <section class="panel"><div class="panel-header"><div><h2>Alterar minha senha</h2><p>A nova senha deve ter ao menos 8 caracteres, com letras, números e caractere especial.</p></div></div><form class="account-password-form" method="POST" action="{{ route('minha-conta.senha') }}">@csrf @method('PUT')
        <label>Senha atual<input type="password" name="senha_atual" autocomplete="current-password" required autofocus></label>
        <label>Nova senha<input type="password" name="password" autocomplete="new-password" minlength="8" required></label>
        <label>Confirmar nova senha<input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></label>
        <div class="password-guidance"><i class="bi bi-info-circle"></i> Após a alteração, as demais sessões serão encerradas.</div>
        <button type="submit"><i class="bi bi-check-lg"></i> Alterar senha</button>
    </form></section>
</div>
@endsection
