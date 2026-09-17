<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TerceirosCR</title>

    <!-- Fonte -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons (ÚNICO pacote de ícones) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS Global -->
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">

    <link rel="icon" href="{{ asset('img/icone.ico') }}" type="image/x-icon">

    @stack('styles')
</head>

<body>

    <header class="mobile-header">
        <button type="button" id="menu-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menu">
            <i class="bi bi-list"></i>
        </button>
        <img src="{{ asset('img/logo.png') }}" alt="TerceirosCR">
    </header>

    {{-- MENU LATERAL --}}
    @include('layouts.sidebar')

    {{-- CONTEÚDO PRINCIPAL --}}
    <main class="content">
        @yield('content')
    </main>

    <script>
        (() => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggle = document.getElementById('menu-toggle');
            const close = document.getElementById('sidebar-close');
            const setOpen = open => {
                sidebar.classList.toggle('is-open', open);
                overlay.hidden = !open;
                document.body.classList.toggle('menu-open', open);
                toggle.setAttribute('aria-expanded', String(open));
            };
            toggle.addEventListener('click', () => setOpen(true));
            close.addEventListener('click', () => setOpen(false));
            overlay.addEventListener('click', () => setOpen(false));
            document.addEventListener('keydown', event => { if (event.key === 'Escape') setOpen(false); });
        })();
    </script>

</body>
</html>
