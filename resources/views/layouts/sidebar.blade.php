<aside class="sidebar">

    {{-- LOGO --}}
    <div class="sidebar-logo">
        <img src="{{ asset('img/logo.png') }}" alt="TerceirosCR">
    </div>

    {{-- USUÁRIO --}}
    <div class="user-info">
        <span class="user-name">{{ auth()->user()->name }}</span>
    </div>

    {{-- MENU --}}
    <nav class="sidebar-menu">
        <ul>

            <li>
                <a href="{{ route('home') }}"
                   class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house"></i>
                    <span>Home</span>
                </a>
            </li>

            <li>
                <a href="{{ route('servicos.index') }}"
                   class="{{ request()->is('servicos*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    <span>Serviços</span>
                </a>
            </li>

            <li>
                <a href="{{ route('empresas.index') }}"
                   class="{{ request()->is('empresas*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i>
                    <span>Empresas</span>
                </a>
            </li>

            <li>
                <a href="{{ route('funcionarios.index') }}"
                   class="{{ request()->is('funcionarios*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Funcionários</span>
                </a>
            </li>

            {{-- ADMIN --}}
            @if(auth()->user()->permissao === 'Administrador')
                <li class="menu-section">Administração</li>

                <li>
                    <a href="{{ route('usuarios.index') }}"
                       class="{{ request()->is('usuarios*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Usuários</span>
                    </a>
                </li>
            @endif

            {{-- DIVISOR --}}
            <li class="menu-divider"></li>

            {{-- LOGOUT --}}
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair</span>
                    </button>
                </form>
            </li>

        </ul>
    </nav>

</aside>
