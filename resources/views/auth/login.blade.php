<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acessar sistema | TerceirosCR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/icone.ico') }}" type="image/x-icon">
</head>
<body>
    <main class="login-page">
        <section class="brand-panel" aria-label="Apresentação do sistema">
            <div class="brand-content">
                <img src="{{ asset('img/logo.png') }}" alt="Costa Rica Malhas" class="brand-logo">
                <div class="brand-copy">
                    <span class="brand-kicker">Controle de terceiros</span>
                    <h1>Acesso seguro e organizado para sua operação.</h1>
                    <p>Consulte documentos, acompanhe solicitações e libere acessos com mais segurança.</p>
                </div>
            </div>
            <span class="brand-footer">Ambiente de uso interno</span>
        </section>

        <section class="access-panel">
            <div class="login-card">
                <div class="mobile-brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Costa Rica Malhas">
                </div>

                <header class="login-header">
                    <span class="eyebrow">TerceirosCR</span>
                    <h2>Bem-vindo</h2>
                    <p>Informe seus dados para acessar o sistema.</p>
                </header>

                @if (session('status'))
                    <div class="alert alert-success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username">Usuário</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Digite seu usuário">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <div class="input-wrap password-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Digite sua senha">
                            <button type="button" class="password-toggle" id="password-toggle" aria-label="Mostrar senha" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A9.5 9.5 0 0 1 12 6c6.5 0 10 6 10 6a15 15 0 0 1-2.1 2.8M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6a9.7 9.7 0 0 0 3-.5"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="remember-option" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Manter conectado neste dispositivo</span>
                    </label>

                    <button type="submit" class="login-button">Entrar no sistema</button>
                </form>

                <a class="forgot-password" href="{{ route('senha.solicitar') }}">Esqueci minha senha</a>

                <p class="access-help">Problemas para acessar? Procure o administrador do sistema.</p>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const input = document.getElementById('password');
            const button = document.getElementById('password-toggle');
            button.addEventListener('click', () => {
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                button.classList.toggle('is-visible', !visible);
                button.setAttribute('aria-pressed', String(!visible));
                button.setAttribute('aria-label', visible ? 'Mostrar senha' : 'Ocultar senha');
                input.focus();
            });
        })();
    </script>
</body>
</html>
