<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Auditoria;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route($this->rotaInicial(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(
            [
                'username' => 'required|string',
                'password' => 'required|string',
            ],
            [
                'username.required' => 'Informe o usuário.',
                'password.required' => 'Informe a senha.',
            ]
        );

        $user = Usuario::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            $this->auditar($request, $user, 'Login recusado', 'Credenciais inválidas');
            return back()
                ->withErrors(['username' => 'As credenciais fornecidas são inválidas.'])
                ->onlyInput('username');
        }

        if (isset($user->ativo) && ! $user->ativo) {
            $this->auditar($request, $user, 'Login recusado', 'Conta inativa');
            return back()
                ->withErrors(['username' => 'Usuário inativo. Procure o administrador.'])
                ->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('ultima_atividade_em', now()->timestamp);
        $this->auditar($request, $user, 'Login realizado', 'Autenticação concluída');

        if ($user->trocar_senha) {
            return redirect()->route('minha-conta.index')
                ->with('warning', 'Troque a senha provisória para continuar.');
        }

        return redirect()->route($this->rotaInicial($user));
    }

    public function logout(Request $request)
    {
        $this->auditar($request, $request->user(), 'Logout realizado', 'Sessão encerrada pelo usuário');
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Sessão encerrada com sucesso.');
    }

    private function rotaInicial(Usuario $usuario): string
    {
        return match ($usuario->permissao) {
            'Guarita' => 'guarita.index',
            'Solicitante' => 'servicos.index',
            'Segurança do Trabalho' => 'funcionarios.index',
            default => 'home',
        };
    }

    private function auditar(Request $request, ?Usuario $usuario, string $acao, string $resultado): void
    {
        Auditoria::create([
            'usuario_id' => optional($usuario)->id,
            'modulo' => 'Autenticação',
            'acao' => $acao,
            'registro_tipo' => Usuario::class,
            'registro_id' => optional($usuario)->id,
            'dados_novos' => [
                'usuario_informado' => (string) $request->input('username', optional($usuario)->username),
                'resultado' => $resultado,
            ],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
