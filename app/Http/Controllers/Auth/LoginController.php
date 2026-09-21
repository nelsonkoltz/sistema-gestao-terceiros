<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

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
            return back()
                ->withErrors(['username' => 'As credenciais fornecidas são inválidas.'])
                ->onlyInput('username');
        }

        if (isset($user->ativo) && ! $user->ativo) {
            return back()
                ->withErrors(['username' => 'Usuário inativo. Procure o administrador.'])
                ->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->trocar_senha) {
            return redirect()->route('minha-conta.index')
                ->with('warning', 'Troque a senha provisória para continuar.');
        }

        return redirect()->route($this->rotaInicial($user));
    }

    public function logout(Request $request)
    {
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
}
