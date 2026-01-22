@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center"
     style="min-height: calc(100vh - 80px);">

    <div class="container welcome-container">
    <div class="welcome-box">
        <h1 class="welcome-title">Bem-vindo ao TerceirosCR</h1>
        <p class="welcome-subtitle">Sistema de Gestão de Terceiros</p>

        <div class="welcome-user">
            {{ auth()->user()->name }}
        </div>

        <p class="welcome-text">
            Utilize o menu lateral para acessar os módulos disponíveis
            de acordo com o seu perfil.
        </p>
    </div>
</div>


</div>
@endsection
