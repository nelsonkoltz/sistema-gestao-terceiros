<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::with('filiais')
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
        $filiais = Filial::where('ativa', true)->orderBy('nome')->get();
        return view('usuarios.create', compact('filiais'));
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
            'filiais' => 'nullable|array|min:1',
            'filiais.*' => 'integer|exists:filiais,id',
            'filial_principal_id' => 'nullable|integer|exists:filiais,id',
        ]);

        $usuario = Usuario::create([
            'name'      => $request->name,
            'setor'     => $request->setor,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'trocar_senha' => true,
            'permissao' => $request->permissao,
            'ativo'     => $request->boolean('ativo'),
        ]);
        $this->sincronizarFiliais($request, $usuario);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function show(Usuario $usuario)
    {
        $usuario->load(['inativadoPor', 'filiais']);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(Usuario $usuario)
    {
        $usuario->load('filiais');
        $filiais = Filial::where('ativa', true)->orWhereHas('usuarios', fn ($q) => $q->where('usuarios.id', $usuario->id))->orderBy('nome')->get();
        return view('usuarios.edit', compact('usuario', 'filiais'));
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
            'filiais' => 'nullable|array|min:1',
            'filiais.*' => 'integer|exists:filiais,id',
            'filial_principal_id' => 'nullable|integer|exists:filiais,id',
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
        $this->sincronizarFiliais($request, $usuario);

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

    private function sincronizarFiliais(Request $request, Usuario $usuario): void
    {
        if ($request->has('filiais')) {
            $filialIds = collect($request->input('filiais', []))->map(fn ($id) => (int) $id)->unique()->values();
        } elseif ($usuario->filiais()->exists()) {
            return;
        } else {
            $filialIds = collect([Filial::where('ativa', true)->orderBy('id')->value('id')])->filter();
        }

        if ($filialIds->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['filiais' => 'Selecione pelo menos uma filial.']);
        }
        $principal = (int) ($request->input('filial_principal_id') ?: $filialIds->first());
        if (! $filialIds->contains($principal)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['filial_principal_id' => 'A filial principal deve estar entre as filiais permitidas.']);
        }
        $usuario->filiais()->sync($filialIds->mapWithKeys(fn ($id) => [$id => ['principal' => $id === $principal]])->all());
    }
}
