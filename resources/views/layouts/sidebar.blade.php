@php
    $currentUser = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($currentUser->name)))
        ->filter()->take(2)->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp

<aside class="sidebar" id="sidebar" aria-label="Navegação principal">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}" aria-label="Ir para o início"><img src="{{ asset('img/logo.png') }}" alt="TerceirosCR"></a>
        <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Fechar menu"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="sidebar-user">
        <span class="user-avatar" aria-hidden="true">{{ $initials }}</span>
        <span class="user-details">
            <strong title="{{ $currentUser->name }}">{{ $currentUser->name }}</strong>
            <small>{{ $currentUser->permissao }}{{ $currentUser->setor ? ' · '.$currentUser->setor : '' }}</small>
        </span>
    </div>

    <nav class="sidebar-menu">
        <span class="menu-label">Principal</span>
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif><i class="bi bi-grid"></i><span>Visão geral</span></a>
        <a href="{{ route('servicos.index') }}" class="{{ request()->is('servicos*') ? 'active' : '' }}" @if(request()->is('servicos*')) aria-current="page" @endif><i class="bi bi-clipboard-check"></i><span>Solicitações</span></a>

        <span class="menu-label">Cadastros</span>
        <a href="{{ route('empresas.index') }}" class="{{ request()->is('empresas*') ? 'active' : '' }}" @if(request()->is('empresas*')) aria-current="page" @endif><i class="bi bi-buildings"></i><span>Empresas</span></a>
        <a href="{{ route('funcionarios.index') }}" class="{{ request()->is('funcionarios*') ? 'active' : '' }}" @if(request()->is('funcionarios*')) aria-current="page" @endif><i class="bi bi-person-vcard"></i><span>Funcionários</span></a>

        @if($currentUser->permissao === 'Administrador')
            <span class="menu-label">Administração</span>
            <a href="{{ route('usuarios.index') }}" class="{{ request()->is('usuarios*') ? 'active' : '' }}" @if(request()->is('usuarios*')) aria-current="page" @endif><i class="bi bi-people"></i><span>Usuários</span></a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn"><i class="bi bi-box-arrow-left"></i><span>Sair</span></button>
        </form>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay" hidden></div>
