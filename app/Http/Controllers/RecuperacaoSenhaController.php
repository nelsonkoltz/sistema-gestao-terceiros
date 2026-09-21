<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\RecuperacaoSenha;
use App\Models\Usuario;
use App\Services\AlertaDocumentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RecuperacaoSenhaController extends Controller
{
    public function solicitar() { return view('auth.esqueci-senha'); }

    public function enviar(Request $request, AlertaDocumentoService $emailService)
    {
        $request->validate(['email' => 'required|email|max:255']);
        $mensagem = 'Se o e-mail estiver vinculado a uma conta ativa, você receberá as instruções em instantes.';
        $usuario = Usuario::where('email', $request->email)->where('ativo', true)->first();
        if (!$usuario) return back()->with('status', $mensagem);

        RecuperacaoSenha::where('usuario_id', $usuario->id)->whereNull('utilizado_em')->update(['utilizado_em' => now()]);
        $token = Str::random(64);
        RecuperacaoSenha::create([
            'usuario_id' => $usuario->id, 'token_hash' => hash('sha256', $token),
            'expira_em' => now()->addMinutes(30), 'ip_solicitacao' => $request->ip(),
        ]);

        $podeEnviar = $emailService->configurarEmail() || app()->environment('testing');
        if ($podeEnviar) {
            try {
                $url = route('senha.redefinir', ['token' => $token]);
                Mail::send('emails.recuperacao-senha', compact('usuario', 'url'), function ($mail) use ($usuario) {
                    $mail->to($usuario->email, $usuario->name)->subject('🔐 Redefinição de senha — TerceirosCR');
                });
            } catch (\Throwable $e) { report($e); }
        }
        $this->auditar($usuario, 'Recuperação solicitada', $request, ['email_destino' => $this->mascarar($usuario->email)]);
        return back()->with('status', $mensagem);
    }

    public function redefinir(string $token)
    {
        $registro = $this->registroValido($token);
        abort_unless($registro, 404);
        return view('auth.redefinir-senha', compact('token'));
    }

    public function atualizar(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:64',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ], ['password.regex' => 'Use letras, números e pelo menos um caractere especial.']);
        $registro = $this->registroValido($request->token);
        if (!$registro) return back()->withErrors(['token' => 'Este link é inválido, expirou ou já foi utilizado.']);

        DB::transaction(function () use ($registro, $request) {
            $registro->usuario->update([
                'password' => Hash::make($request->password), 'trocar_senha' => false,
                'senha_alterada_em' => now(),
            ]);
            $registro->usuario->forceFill(['remember_token' => Str::random(60)])->save();
            $registro->update(['utilizado_em' => now()]);
        });
        $this->auditar($registro->usuario, 'Senha redefinida', $request, ['metodo' => 'link de recuperação']);
        return redirect()->route('login')->with('status', 'Senha redefinida com sucesso. Você já pode entrar.');
    }

    private function registroValido(string $token): ?RecuperacaoSenha
    {
        return RecuperacaoSenha::with('usuario')->where('token_hash', hash('sha256', $token))
            ->whereNull('utilizado_em')->where('expira_em', '>', now())
            ->whereHas('usuario', fn ($q) => $q->where('ativo', true))->first();
    }

    private function auditar(Usuario $usuario, string $acao, Request $request, array $dados): void
    {
        Auditoria::create(['usuario_id' => $usuario->id, 'modulo' => 'Autenticacao', 'acao' => $acao,
            'registro_tipo' => Usuario::class, 'registro_id' => $usuario->id, 'dados_novos' => $dados,
            'ip' => $request->ip(), 'user_agent' => mb_substr((string)$request->userAgent(), 0, 1000)]);
    }

    private function mascarar(string $email): string
    {
        [$nome, $dominio] = array_pad(explode('@', $email, 2), 2, '');
        return mb_substr($nome, 0, 2).'***@'.$dominio;
    }
}
