{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - TerceirosCR</title>

  {{-- Fonte --}}
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  {{-- CSS --}}
  <link href="{{ asset('css/login.css') }}" rel="stylesheet">

</head>

<body>

  <div class="container">
    <h2>TerceirosCR</h2>
    <p class="subtitle">Acesso ao sistema</p>

    {{-- STATUS --}}
    @if (session('status'))
      <div class="alert alert-info">
        {{ session('status') }}
      </div>
    @endif

    {{-- ERROS --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $erro)
            <li>{{ $erro }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
      @csrf

      {{-- USERNAME --}}
      <div class="form-group">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" required
          autofocus autocomplete="username">
      </div>

      {{-- PASSWORD --}}
      <div class="form-group">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" class="form-control" required
          autocomplete="current-password">
      </div>

      {{-- REMEMBER --}}
      <div class="form-remember">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember">Manter conectado</label>
      </div>

      <button type="submit" class="btn-primary">
        Entrar
      </button>
    </form>
  </div>

</body>

</html>