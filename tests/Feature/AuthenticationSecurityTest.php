<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Configuracao;
use App\Models\RecuperacaoSenha;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'Administrador'): Usuario
    {
        return Usuario::create([
            'name' => 'Usuário Segurança',
            'username' => 'seguranca-'.uniqid(),
            'password' => Hash::make('SenhaTeste123!'),
            'permissao' => $role,
            'ativo' => true,
        ]);
    }

    public function test_login_logout_and_failed_attempts_are_audited(): void
    {
        $user = $this->user();

        $this->post('/login', ['username' => 'usuario-inexistente', 'password' => 'incorreta'])
            ->assertSessionHasErrors('username');
        $this->assertDatabaseHas('auditorias', ['usuario_id' => null, 'acao' => 'Login recusado']);
        $failed = Auditoria::where('acao', 'Login recusado')->firstOrFail();
        $this->assertSame('usuario-inexistente', $failed->dados_novos['usuario_informado']);

        $this->post('/login', ['username' => $user->username, 'password' => 'SenhaTeste123!'])
            ->assertRedirect('/');
        $this->assertDatabaseHas('auditorias', ['usuario_id' => $user->id, 'acao' => 'Login realizado']);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertDatabaseHas('auditorias', ['usuario_id' => $user->id, 'acao' => 'Logout realizado']);
    }

    public function test_inactive_account_attempt_is_audited(): void
    {
        $user = $this->user();
        $user->update(['ativo' => false]);

        $this->post('/login', ['username' => $user->username, 'password' => 'SenhaTeste123!'])
            ->assertSessionHasErrors('username');
        $this->assertDatabaseHas('auditorias', ['usuario_id' => $user->id, 'acao' => 'Login recusado']);
        $this->assertSame('Conta inativa', Auditoria::latest()->first()->dados_novos['resultado']);
    }

    public function test_admin_configures_timeout_and_idle_session_expires(): void
    {
        Carbon::setTestNow('2026-09-21 18:00:00');
        $admin = $this->user();
        $this->actingAs($admin)->put('/configuracoes/sessao', ['minutos' => 15])
            ->assertSessionHas('success');
        $this->assertSame('15', Configuracao::valor('sessao_inatividade_minutos'));

        $this->withSession(['ultima_atividade_em' => now()->subMinutes(16)->timestamp])
            ->get('/')
            ->assertRedirect('/login')
            ->assertSessionHas('status');
        $this->assertGuest();
        $this->assertDatabaseHas('auditorias', ['usuario_id' => $admin->id, 'acao' => 'Sessão expirada']);
        Carbon::setTestNow();
    }

    public function test_expired_and_used_password_tokens_are_cleaned(): void
    {
        $user = $this->user();
        RecuperacaoSenha::create(['usuario_id' => $user->id, 'token_hash' => hash('sha256', 'expirado'), 'expira_em' => now()->subMinute()]);
        RecuperacaoSenha::create(['usuario_id' => $user->id, 'token_hash' => hash('sha256', 'usado'), 'expira_em' => now()->addHour(), 'utilizado_em' => now()]);
        RecuperacaoSenha::create(['usuario_id' => $user->id, 'token_hash' => hash('sha256', 'valido'), 'expira_em' => now()->addHour()]);

        $this->artisan('senhas:limpar-recuperacoes')->assertExitCode(0);
        $this->assertSame(1, RecuperacaoSenha::count());
        $this->assertDatabaseHas('recuperacoes_senha', ['token_hash' => hash('sha256', 'valido')]);
    }
}
