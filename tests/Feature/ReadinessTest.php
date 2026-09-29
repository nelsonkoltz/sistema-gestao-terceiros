<?php

namespace Tests\Feature;

use App\Models\{Documento, DocumentoFuncionario, Empresa, Filial, Funcionario, RecuperacaoSenha, RegistroAcesso, Servico, Setor, Usuario};
use App\Services\AlertaDocumentoService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Auth, DB, Hash, Storage};
use Tests\TestCase;

class ReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-29 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(string $role = 'Administrador'): Usuario
    {
        $user = Usuario::create(['name' => 'Teste', 'username' => uniqid('user'), 'email' => uniqid().'@example.test',
            'password' => Hash::make('SenhaTeste123!'), 'permissao' => $role, 'ativo' => true]);
        $user->filiais()->attach(Filial::where('codigo', 'MATRIZ')->value('id'), ['principal' => true]);
        return $user;
    }

    private function operation(Usuario $user, ?Filial $branch = null, string $description = 'Manutenção'): array
    {
        $company = Empresa::create(['nome' => uniqid('Empresa'), 'cnpj' => '12345678000190', 'tipo' => 'CNPJ',
            'telefone' => '47999999999', 'email' => 'empresa@example.test', 'endereco_rua' => 'Rua Teste',
            'endereco_numero' => '1', 'endereco_bairro' => 'Centro', 'endereco_cidade' => 'Blumenau',
            'endereco_cep' => '89000000', 'endereco_estado' => 'SC', 'ativo' => true]);
        $employee = Funcionario::create(['nome' => uniqid('Pessoa'), 'cpf' => (string) random_int(10000000000, 99999999999),
            'empresa_id' => $company->id, 'ativo' => true]);
        Documento::create(['empresa_id' => $company->id, 'nome_arquivo' => 'contrato.pdf', 'caminho_arquivo' => 'contrato.pdf']);
        DocumentoFuncionario::create(['funcionario_id' => $employee->id, 'nome_original' => 'aso.pdf', 'path' => 'aso.pdf']);
        $sector = Setor::firstOrCreate(['nome' => 'TI']);
        $service = new Servico(['empresa_id' => $company->id, 'solicitante_id' => $user->id, 'setor_id' => $sector->id,
            'descricao' => $description, 'vai_almocar' => false, 'status' => 'Agendado', 'data_servico' => today(),
            'hora_inicio' => '08:00:00', 'hora_fim' => '18:00:00']);
        $service->filial_id = $branch?->id ?? Filial::where('codigo', 'MATRIZ')->value('id');
        $service->save();
        return [$company, $employee, $service];
    }

    public function test_services_and_direct_urls_are_isolated_and_branch_switch_is_authorized(): void
    {
        $user = $this->user('Solicitante');
        $other = Filial::create(['nome' => 'Sul', 'codigo' => 'SUL', 'ativa' => true]);
        [, , $local] = $this->operation($user, null, 'SERVICO-MATRIZ');
        [, , $remote] = $this->operation($user, $other, 'SERVICO-SUL');
        $this->actingAs($user)->get('/servicos')->assertOk()->assertSee('SERVICO-MATRIZ')->assertDontSee('SERVICO-SUL');
        $this->get('/servicos/'.$remote->id)->assertNotFound();
        $this->delete('/servicos/'.$remote->id)->assertNotFound();
        $this->post('/servicos/'.$remote->id.'/cancelar', ['motivo_cancelamento' => 'Não autorizado'])->assertNotFound();
        $this->post('/filial-ativa', ['filial_id' => $other->id])->assertForbidden();
        $user->filiais()->attach($other->id);
        $this->post('/filial-ativa', ['filial_id' => $other->id])->assertRedirect('/');
        $this->get('/servicos')->assertOk()->assertSee('SERVICO-SUL')->assertDontSee('SERVICO-MATRIZ');
        $this->get('/servicos/'.$local->id)->assertNotFound();
        $user->filiais()->detach($other->id);
        $this->withSession(['filial_ativa_id' => $other->id])->get('/servicos')
            ->assertSee('SERVICO-MATRIZ')->assertDontSee('SERVICO-SUL');
    }

    public function test_gate_history_exports_occurrences_and_route_binding_are_isolated(): void
    {
        $gate = $this->user('Guarita');
        $other = Filial::create(['nome' => 'Sul', 'codigo' => 'SUL', 'ativa' => true]);
        [, $employee, $service] = $this->operation($gate, $other, 'SEGREDO-OUTRA-FILIAL');
        $record = new RegistroAcesso(['funcionario_id' => $employee->id, 'servico_id' => $service->id,
            'registrado_por' => $gate->id, 'decisao' => 'Liberado', 'entrada_em' => now()]);
        $record->filial_id = $other->id;
        $record->save();
        $this->actingAs($gate)->get('/guarita')->assertOk()->assertDontSee('SEGREDO-OUTRA-FILIAL');
        $this->get('/guarita/historico')->assertOk()->assertDontSee($employee->nome);
        $csv = $this->get('/guarita/historico/exportar')->assertOk()->streamedContent();
        $this->assertStringNotContainsString('SEGREDO-OUTRA-FILIAL', $csv);
        $this->put('/guarita/registros/'.$record->id.'/saida')->assertNotFound();
        $this->post('/guarita/funcionarios/'.$employee->id.'/ocorrencia', [
            'categoria' => 'Outro', 'observacao' => 'Tentativa', 'servico_id' => $service->id,
        ])->assertSessionHas('error');
        $this->post('/guarita/funcionarios/'.$employee->id.'/entrada')->assertSessionHas('error');
        $this->assertDatabaseHas('registros_acesso', ['funcionario_id' => $employee->id,
            'filial_id' => Filial::where('codigo', 'MATRIZ')->value('id'), 'decisao' => 'Bloqueado']);
    }

    public function test_accounts_without_active_branches_cannot_operate(): void
    {
        $user = $this->user('Guarita');
        $user->filiais()->detach();
        $this->actingAs($user)->get('/guarita')->assertForbidden();
        $this->get('/minha-conta')->assertOk();
        $user->filiais()->attach(Filial::first()->id);
        Filial::first()->update(['ativa' => false]);
        $this->get('/guarita')->assertForbidden();
    }

    public function test_admin_consolidated_dashboard_does_not_change_operational_branch(): void
    {
        $admin = $this->user();
        $other = Filial::create(['nome' => 'Sul', 'codigo' => 'SUL', 'ativa' => true]);
        $this->operation($admin);
        $this->operation($admin, $other);
        $this->actingAs($admin)->get('/')->assertViewHas('counts', fn ($counts) => $counts['pendentes'] === 1);
        $this->get('/?consolidado=1')->assertOk()->assertSee('Todas as filiais')
            ->assertViewHas('counts', fn ($counts) => $counts['pendentes'] === 2)
            ->assertViewHas('resumoFiliais', fn ($rows) => $rows->count() === 2);
        $this->get('/servicos')->assertViewHas('servicos', fn ($services) => $services->total() === 1);
    }

    public function test_client_cannot_override_branch_when_creating_service(): void
    {
        $user = $this->user('Solicitante');
        $other = Filial::create(['nome' => 'Sul', 'codigo' => 'SUL', 'ativa' => true]);
        [$company, , $service] = $this->operation($user);
        $this->actingAs($user)->post('/servicos', ['empresa_id' => $company->id, 'setor_id' => $service->setor_id,
            'descricao' => 'NOVO-LOCAL', 'status' => 'Agendado', 'data_servico' => '2026-09-29',
            'hora_inicio' => '08:00', 'hora_fim' => '18:00', 'vai_almocar' => 0, 'filial_id' => $other->id])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('servicos', ['descricao' => 'NOVO-LOCAL', 'filial_id' => $service->filial_id]);
    }

    public function test_password_change_invalidates_other_session_but_keeps_current_session(): void
    {
        $user = $this->user();
        $this->post('/login', ['username' => $user->username, 'password' => 'SenhaTeste123!'])->assertRedirect('/');
        $oldHash = session('password_hash_web');
        $this->assertNotEmpty($oldHash);
        $this->put('/minha-conta/senha', ['senha_atual' => 'SenhaTeste123!', 'password' => 'SenhaNova456!',
            'password_confirmation' => 'SenhaNova456!'])->assertSessionHasNoErrors();
        $this->get('/')->assertOk();
        $this->assertNotSame($oldHash, session('password_hash_web'));
        Auth::guard()->setUser($user->fresh());
        $this->withSession(['password_hash_web' => $oldHash])->get('/')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_password_reset_invalidates_existing_session_and_all_reset_tokens(): void
    {
        $user = $this->user();
        $token = str_repeat('a', 64);
        foreach ([$token, str_repeat('b', 64)] as $value) RecuperacaoSenha::create([
            'usuario_id' => $user->id, 'token_hash' => hash('sha256', $value), 'expira_em' => now()->addMinutes(30),
        ]);
        $oldHash = $user->password;
        $this->post('/redefinir-senha', ['token' => $token, 'password' => 'NovaSenha456!', 'password_confirmation' => 'NovaSenha456!'])
            ->assertRedirect('/login');
        $this->assertSame(0, RecuperacaoSenha::whereNull('utilizado_em')->count());
        $this->actingAs($user->fresh())->withSession(['password_hash_web' => $oldHash])->get('/')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_cancelled_service_stays_cancelled_after_last_exit_and_exit_is_audited(): void
    {
        $admin = $this->user();
        [, $employee, $service] = $this->operation($admin);
        $this->actingAs($admin)->post('/guarita/funcionarios/'.$employee->id.'/entrada')->assertSessionHas('success');
        $this->post('/guarita/funcionarios/'.$employee->id.'/entrada')->assertSessionHas('error');
        $record = RegistroAcesso::whereNotNull('entrada_em')->firstOrFail();
        $this->post('/servicos/'.$service->id.'/cancelar', ['motivo_cancelamento' => 'Serviço suspenso'])->assertSessionHas('success');
        $this->put('/guarita/registros/'.$record->id.'/saida')->assertSessionHas('success');
        $this->assertSame('Cancelado', $service->fresh()->status);
        $this->assertDatabaseHas('auditorias', ['modulo' => 'RegistroAcesso', 'registro_id' => $record->id, 'acao' => 'Alterado']);
        $this->put('/guarita/registros/'.$record->id.'/saida')->assertStatus(422);
    }

    public function test_database_rejects_duplicate_open_entry_even_without_controller(): void
    {
        $admin = $this->user();
        [, $employee, $service] = $this->operation($admin);
        $data = ['funcionario_id' => $employee->id, 'servico_id' => $service->id,
            'registrado_por' => $admin->id, 'decisao' => 'Liberado', 'entrada_em' => now()];
        RegistroAcesso::create($data);
        try {
            DB::table('registros_acesso')->insert($data);
            $this->fail('A segunda entrada deveria ser rejeitada pelo banco.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('uma_entrada_aberta_por_funcionario', $e->getMessage());
        }
        $this->assertSame(1, RegistroAcesso::whereNotNull('entrada_em')->count());
    }

    public function test_expiry_boundary_is_consistent_in_access_alerts_and_job(): void
    {
        $admin = $this->user();
        [$company, $employee] = $this->operation($admin);
        $document = $company->documentos()->firstOrFail();
        $document->update(['validade_ate' => '2026-09-30']);
        Carbon::setTestNow('2026-09-29 23:59:59');
        $this->assertTrue($company->fresh()->documentacaoRegular());
        Carbon::setTestNow('2026-09-30 00:00:00');
        $this->assertFalse($company->fresh()->documentacaoRegular());
        $this->assertSame('Vencido', $document->fresh()->status_atual);
        $items = app(AlertaDocumentoService::class)->dados()['itens'];
        $this->assertSame('Vencido', $items->firstWhere('nome', $company->nome)['situacao']);
        $this->artisan('documentos:atualizar-validade', ['--force' => true])->assertExitCode(0);
        $this->assertSame('Vencido', $document->fresh()->status);
    }

    public function test_service_allows_entire_scheduled_day_even_with_legacy_hours(): void
    {
        $admin = $this->user();
        [, $employee, $service] = $this->operation($admin);
        foreach (['00:00:00', '07:59:59', '18:00:01', '23:59:59'] as $time) {
            Carbon::setTestNow('2026-09-29 '.$time);
            $this->assertTrue($service->fresh()->autorizaFuncionario($employee->fresh()), $time);
        }
        Carbon::setTestNow('2026-09-30 10:00:00');
        $this->assertFalse($service->fresh()->autorizaFuncionario($employee->fresh()));
    }

    public function test_document_renewal_preserves_file_and_rejects_second_replacement(): void
    {
        Storage::fake('public');
        $admin = $this->user();
        [$company] = $this->operation($admin);
        $old = $company->documentos()->firstOrFail();
        Storage::disk('public')->put($old->caminho_arquivo, 'original');
        $this->actingAs($admin)->post('/empresas/'.$company->id.'/documentos', [
            'arquivo' => UploadedFile::fake()->create('novo.pdf', 5, 'application/pdf'), 'documento_anterior_id' => $old->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('Substituido', $old->fresh()->status);
        Storage::disk('public')->assertExists($old->caminho_arquivo);
        $this->post('/empresas/'.$company->id.'/documentos', [
            'arquivo' => UploadedFile::fake()->create('duplicado.pdf', 5, 'application/pdf'), 'documento_anterior_id' => $old->id,
        ])->assertSessionHasErrors('arquivo');
        $this->assertSame(1, $old->posteriores()->count());
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_csv_exports_neutralize_formulas(): void
    {
        $admin = $this->user();
        [, $employee] = $this->operation($admin);
        $this->actingAs($admin)->post('/guarita/funcionarios/'.$employee->id.'/ocorrencia', [
            'categoria' => 'Outro', 'observacao' => '=1+1',
        ])->assertSessionHas('success');
        $csv = $this->get('/guarita/ocorrencias/exportar')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=1+1", $csv);
    }

    public function test_admin_can_read_inactive_branch_history_but_cannot_write(): void
    {
        $admin = $this->user();
        $branch = Filial::create(['nome' => 'Encerrada', 'codigo' => 'ENCERRADA', 'ativa' => false]);
        [, $employee, $service] = $this->operation($admin, $branch, 'HISTORICO-ENCERRADO');
        $this->actingAs($admin)->post('/filial-ativa', ['filial_id' => $branch->id])->assertRedirect('/');
        $this->get('/servicos')->assertOk()->assertSee('HISTORICO-ENCERRADO');
        $this->get('/guarita/historico')->assertOk();
        $this->post('/guarita/funcionarios/'.$employee->id.'/entrada')->assertForbidden();
        $this->post('/servicos/'.$service->id.'/cancelar', ['motivo_cancelamento' => 'Teste inativa'])->assertForbidden();
    }

    public function test_invalid_branch_assignment_does_not_partially_create_user(): void
    {
        $admin = $this->user();
        $other = Filial::create(['nome' => 'Sul', 'codigo' => 'SUL', 'ativa' => true]);
        $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Inválido', 'username' => 'sem-filial-valida', 'email' => 'invalido@example.test', 'setor' => 'TI',
            'permissao' => 'Solicitante', 'ativo' => 1, 'password' => 'SenhaNova123!', 'password_confirmation' => 'SenhaNova123!',
            'filiais' => [Filial::where('codigo', 'MATRIZ')->value('id')], 'filial_principal_id' => $other->id,
        ])->assertSessionHasErrors('filial_principal_id');
        $this->assertDatabaseMissing('usuarios', ['username' => 'sem-filial-valida']);
    }

    public function test_smtp_failure_does_not_prevent_document_expiration(): void
    {
        $admin = $this->user();
        [$company] = $this->operation($admin);
        $document = $company->documentos()->firstOrFail();
        $document->update(['validade_ate' => today()]);
        $service = \Mockery::mock(AlertaDocumentoService::class)->makePartial();
        $service->shouldReceive('configurarEmail')->once()->andReturn(true);
        $this->app->instance(AlertaDocumentoService::class, $service);
        \Illuminate\Support\Facades\Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP indisponível'));
        $this->artisan('documentos:enviar-alertas', ['--force' => true])->assertExitCode(1);
        $this->artisan('documentos:atualizar-validade', ['--force' => true])->assertExitCode(0);
        $this->assertSame('Vencido', $document->fresh()->status);
        $this->assertFalse($company->fresh()->documentacaoRegular());
    }

    public function test_smtp_tls_settings_are_applied_to_new_transport(): void
    {
        foreach (['mail_ativo' => '1', 'mail_senha' => \Illuminate\Support\Facades\Crypt::encryptString('teste'),
            'mail_host' => 'smtp.example.test', 'mail_porta' => '587', 'mail_criptografia' => 'tls',
            'mail_usuario' => 'user@example.test', 'mail_remetente_email' => 'user@example.test'] as $key => $value) {
            \App\Models\Configuracao::updateOrCreate(['chave' => $key], ['valor' => $value]);
        }
        $service = app(AlertaDocumentoService::class);
        $this->assertTrue($service->configurarEmail());
        $transport = \Illuminate\Support\Facades\Mail::mailer('smtp')->getSymfonyTransport();
        $this->assertTrue($transport->isTlsRequired());
        \App\Models\Configuracao::where('chave', 'mail_criptografia')->update(['valor' => 'ssl']);
        \App\Models\Configuracao::where('chave', 'mail_porta')->update(['valor' => '465']);
        $this->assertTrue($service->configurarEmail());
        $newTransport = \Illuminate\Support\Facades\Mail::mailer('smtp')->getSymfonyTransport();
        $this->assertNotSame($transport, $newTransport);
        $this->assertTrue($newTransport->getStream()->isTLS());
    }
}
