<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TerceirosCR</title>

    <!-- Ícones e Fontes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">



    <!-- CSS Global -->

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/geral.css') }}" rel="stylesheet">

    <link rel="icon" href="{{ asset('img/icone.ico')}}" sizes="32x32" type="image/x-icon">


    @stack('styles')
</head>

<body>

    <!-- Menu Lateral -->
    <div class="sidebar">
        <!-- Logo -->
        <div class="sidebar-logo">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
        </div>

        <!-- Informações do Usuário -->
        <div class="user-info">
            <span class="user-name">{{ auth()->user()->name }}</span>
        </div>

        <!-- Links do Menu -->
        <ul>
            <!-- Home -->
            <li>
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>

            <!-- Serviços -->
            <li>
                <a href="{{ route('servicos.index') }}" class="{{ request()->is('servicos*') ? 'active' : '' }}">
                    <i class="fas fa-cogs"></i> Serviços
                </a>
            </li>

            <!-- Empresas -->
            <li>
                <a href="{{ route('empresas.index') }}" class="{{ request()->is('empresas*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> Empresas
                </a>
            </li>

            <!-- Funcionários -->
            <li>
                <a href="{{ route('funcionarios.index') }}"
                    class="{{ request()->is('funcionarios*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Funcionários
                </a>
            </li>

            <!-- Usuários (somente Administrador) -->
            @if(auth()->user()->permissao === 'Administrador')
                <li>
                    <a href="{{ route('usuarios.index') }}" class="{{ request()->is('usuarios*') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i> Usuários
                    </a>
                </li>
            @endif

            <!-- Logout -->
            <li>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <!-- Formulário oculto para logout -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Conteúdo Principal -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Destaca o item ativo ao clicar no menu
        const menuItems = document.querySelectorAll('.sidebar ul li a');
        menuItems.forEach(item => {
            item.addEventListener('click', function () {
                menuItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });
    </script>

</body>

</html>