{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - TerceirosCR</title>

  {{-- Fontes e CSS (ajuste se usar Vite) --}}
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>
<body>
  <div class="container">
    <h2>Login</h2>

    {{-- Mensagem de status (logout, etc.) --}}
    @if (session('status'))
      <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    {{-- Erros de validação --}}
    @if ($errors->any())
      <div class="alert alert-danger" role="alert">
        <ul style="margin:0;padding-left:18px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Form de login (POST /login) --}}
    <form method="POST" action="{{ route('login.post') }}">
      @csrf

      <div class="form-group">
        <label for="username">Nome de usuário</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          value="{{ old('username') }}"
          required
          autofocus
          autocomplete="username"
        >
        @error('username')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label for="password">Senha</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          required
          autocomplete="current-password"
        >
        @error('password')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group" style="display:flex;align-items:center;gap:.5rem;">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember" style="margin:0;">Manter conectado</label>
      </div>

      <button type="submit" class="btn btn-primary">Login</button>
    </form>

    {{-- Opcional: link de recuperação de senha (ajuste a rota se usar) --}}
    {{-- <div style="margin-top:12px;">
      <a href="{{ route('password.request') }}">Esqueci minha senha</a>
    </div> --}}
  </div>
</body>
</html>
