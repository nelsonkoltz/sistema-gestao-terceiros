<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::orderByDesc('id')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'setor' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:usuarios,username',
            'password' => 'required|min:6|confirmed',
            'permissao' => 'required|in:Administrador,Usuário,Consulta',
        ]);

        Usuario::create([
            'name' => $request->name,
            'setor' => $request->setor,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'permissao' => $request->permissao,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
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
            'name' => 'required|string|max:255',
            'setor' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:usuarios,username,' . $usuario->id,
            'permissao' => 'required|in:Administrador,Usuário,Consulta',
        ]);

        $usuario->update([
            'name' => $request->name,
            'setor' => $request->setor,
            'username' => $request->username,
            'email' => $request->email,
            'permissao' => $request->permissao,
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Usuario $usuario)
    {
        if (Auth::id() === $usuario->id) {
            return redirect()->route('usuarios.index')->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
