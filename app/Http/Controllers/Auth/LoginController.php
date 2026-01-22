<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class LoginController extends Controller
{
    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Realiza o login do usuário.
     */
    public function login(Request $request)
    {
        // Validação dos campos
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Busca o usuário na tabela 'usuarios'
        $user = Usuario::where('username', $credentials['username'])->first();

        // Verifica se usuário existe e a senha confere com o hash bcrypt
        if ($user && Hash::check($credentials['password'], $user->password)) {

            // Realiza o login
            Auth::login($user, $request->boolean('remember'));

            // Regenera a sessão (segurança)
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        // Caso falhe, retorna com erro
        return back()
            ->withErrors(['username' => 'As credenciais fornecidas são inválidas.'])
            ->onlyInput('username');
    }

    /**
     * Faz logout do usuário autenticado.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalida a sessão e o token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sessão encerrada com sucesso.');
    }
}
