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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link rel="icon" href="{{ asset('img/icone.ico') }}" type="image/x-icon">

    @stack('styles')
</head>

<body>

    {{-- MENU LATERAL --}}
    @include('layouts.sidebar')

    {{-- CONTEÚDO PRINCIPAL --}}
    <main class="content">
        @yield('content')
    </main>

</body>
</html>
