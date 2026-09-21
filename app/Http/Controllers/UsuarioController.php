<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('username', 'like', '%' . $request->search . '%')
                      ->orWhere('setor', 'like', '%' . $request->search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'setor'     => 'required|string|max:255',
            'username'  => 'required|string|max:100|unique:usuarios,username',
            'email'     => 'required|email|max:255|unique:usuarios,email',
            'password'  => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'permissao' => 'required|in:Administrador,Solicitante,Segurança do Trabalho,Guarita',
            'ativo'     => 'required|boolean',
        ]);

        Usuario::create([
            'name'      => $request->name,
            'setor'     => $request->setor,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'trocar_senha' => true,
            'permissao' => $request->permissao,
            'ativo'     => $request->boolean('ativo'),
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function show(Usuario $usuario)
    {
        $usuario->load('inativadoPor');
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(Usuario $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'setor'     => 'required|string|max:255',
            'username'  => 'required|string|max:100|unique:usuarios,username,' . $usuario->id,
            'email'     => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'password'  => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'permissao' => 'required|in:Administrador,Solicitante,Segurança do Trabalho,Guarita',
            'ativo'     => 'required|boolean',
            'motivo_inativacao' => 'nullable|required_if:ativo,0|string|min:5|max:1000',
        ]);

        $data = $request->only([
            'name',
            'setor',
            'username',
            'email',
            'permissao',
            'ativo',
        ]);

        $data['ativo'] = $request->boolean('ativo');
        if (Auth::id() === $usuario->id && !$data['ativo']) {
            return back()->withErrors(['ativo' => 'Você não pode inativar a própria conta.'])->withInput();
        }
        if ($usuario->ativo && !$data['ativo']) {
            $data['motivo_inativacao'] = trim((string) $request->motivo_inativacao);
            $data['inativado_por_id'] = Auth::id();
            $data['inativado_em'] = now();
        } elseif (!$usuario->ativo && $data['ativo']) {
            $data['motivo_inativacao'] = null;
            $data['inativado_por_id'] = null;
            $data['inativado_em'] = null;
        } elseif (!$data['ativo']) {
            $data['motivo_inativacao'] = trim((string) $request->motivo_inativacao);
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['trocar_senha'] = Auth::id() !== $usuario->id;
            $data['senha_alterada_em'] = Auth::id() === $usuario->id ? now() : null;
            $data['remember_token'] = null;
        }

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function minhaConta()
    {
        return view('usuarios.minha-conta', ['usuario' => Auth::user()]);
    }

    public function alterarMinhaSenha(Request $request)
    {
        $request->validate([
            'senha_atual' => 'required|string',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:senha_atual', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ], [
            'senha_atual.required' => 'Informe sua senha atual.',
            'password.required' => 'Informe a nova senha.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
            'password.regex' => 'Use letras, números e pelo menos um caractere especial.',
        ]);

        $usuario = Auth::user();
        if (!Hash::check($request->senha_atual, $usuario->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        Auth::logoutOtherDevices($request->senha_atual);
        $usuario->update([
            'password' => Hash::make($request->password),
            'trocar_senha' => false,
            'senha_alterada_em' => now(),
        ]);
        $usuario->forceFill(['remember_token' => null])->save();
        $request->session()->regenerate();

        return redirect()->route('minha-conta.index')->with('success', 'Senha alterada com sucesso. As outras sessões foram encerradas.');
    }

    public function destroy(Usuario $usuario)
    {
        if (Auth::id() === $usuario->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        if (\App\Models\Servico::where('solicitante_id', $usuario->id)->exists()) {
            return back()->with('error', 'Este usuário possui serviços vinculados e não pode ser excluído.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }
}
