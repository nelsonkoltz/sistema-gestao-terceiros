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
            'password'  => 'required|min:6|confirmed',
            'permissao' => 'required|in:Administrador,Solicitante,Segurança do Trabalho,Guarita',
        ]);

        Usuario::create([
            'name'      => $request->name,
            'setor'     => $request->setor,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'permissao' => $request->permissao,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function show(Usuario $usuario)
    {
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
            'password'  => 'nullable|min:6|confirmed',
            'permissao' => 'required|in:Administrador,Solicitante,Segurança do Trabalho,Guarita',
        ]);

        $data = $request->only([
            'name',
            'setor',
            'username',
            'email',
            'permissao',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
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
