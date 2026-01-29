@extends('layouts.app')

@section('title', 'Início')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
@php
    $hora = now()->format('H');

    if ($hora >= 5 && $hora < 12) {
        $saudacao = 'Bom dia';
    } elseif ($hora >= 12 && $hora < 18) {
        $saudacao = 'Boa tarde';
    } else {
        $saudacao = 'Boa noite';
    }
@endphp

<div class="home-wrapper">

    <div class="home-card">

        <h1 class="home-title">
            {{ $saudacao }}, {{ auth()->user()->name }}
        </h1>

        <p class="home-subtitle">
            Bem-vindo ao <strong>TerceirosCR</strong>
        </p>

        <p class="home-text">
            Utilize o menu lateral para acessar os módulos disponíveis
            de acordo com o seu perfil de acesso.
        </p>

    </div>

</div>
@endsection
