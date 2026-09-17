<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\DocumentoFuncionario;
use App\Models\Configuracao;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Servico;
use App\Models\Setor;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemTest extends TestCase
{
    use RefreshDatabase;

    private function user($role = 'Administrador')
    {
        return Usuario::create([
            'name' => 'Teste', 'username' => 'teste-' . uniqid(),
            'password' => Hash::make('SenhaTeste123!'), 'permissao' => $role,
        ]);
    }

    private function companyData()
    {
        return [
            'nome' => 'Empresa de Teste', 'cnpj' => '12345678000190',
            'tipo' => 'CNPJ', 'telefone' => '47999999999', 'email' => 'empresa@example.test',
            'endereco_rua' => 'Rua Teste', 'endereco_numero' => '10',
            'endereco_bairro' => 'Centro', 'endereco_cidade' => 'Blumenau',
            'endereco_estado' => 'SC', 'endereco_cep' => '89000000',
        ];
    }

    public function test_login_and_logout()
    {
        $user = $this->user();
        $this->get('/login')->assertOk();
        $this->post('/login', ['username' => $user->username, 'password' => 'SenhaTeste123!'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertOk();
        $this->get('/login')->assertRedirect('/');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_read_only_profile_cannot_mutate_any_module()
    {
        $this->actingAs($this->user('Consulta'));
        foreach (['empresas', 'funcionarios', 'servicos'] as $module) {
            $this->get('/' . $module)->assertOk();
            $this->get('/' . $module . '/create')->assertForbidden();
            $this->post('/' . $module, [])->assertForbidden();
        }
        $company = Empresa::create($this->companyData());
        $this->put('/empresas/' . $company->id, [])->assertForbidden();
        $this->delete('/empresas/' . $company->id)->assertForbidden();
        $this->get('/usuarios')->assertForbidden();
    }

    public function test_company_create_search_edit_and_delete()
    {
        $this->actingAs($this->user());
        $this->post('/empresas', $this->companyData())->assertRedirect('/empresas');
        $company = Empresa::firstOrFail();
        $this->get('/empresas/search?q=Empresa')->assertOk()->assertJsonFragment(['id' => $company->id]);
        $this->get('/empresas/' . $company->id)->assertOk();
        $this->get('/empresas/' . $company->id . '/edit')->assertOk();
        $data = $this->companyData();
        unset($data['cnpj']); // Edit form deliberately omits the read-only number.
        $data['nome'] = 'Empresa Atualizada';
        $this->put('/empresas/' . $company->id, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('empresas', ['id' => $company->id, 'nome' => 'Empresa Atualizada']);
        $this->delete('/empresas/' . $company->id)->assertRedirect('/empresas');
        $this->assertDatabaseMissing('empresas', ['id' => $company->id]);
    }

    public function test_employee_create_edit_show_and_delete()
    {
        $this->actingAs($this->user());
        $company = Empresa::create($this->companyData());
        $data = ['empresa_id' => $company->id, 'nome' => 'Funcionário Teste', 'cpf' => '12345678901', 'ativo' => '1'];
        $this->get('/funcionarios/create')->assertOk();
        $this->post('/funcionarios', $data)->assertSessionHasNoErrors();
        $employee = Funcionario::firstOrFail();
        $this->get('/funcionarios/' . $employee->id)->assertOk();
        $this->get('/funcionarios/' . $employee->id . '/edit')->assertOk();
        $data['ativo'] = '0';
        $this->put('/funcionarios/' . $employee->id, $data)->assertSessionHasNoErrors();
        $this->assertFalse($employee->fresh()->ativo);
        $this->delete('/funcionarios/' . $employee->id)->assertRedirect('/funcionarios');
    }

    public function test_service_workflow_and_date_validation()
    {
        $this->actingAs($this->user());
        $company = Empresa::create($this->companyData());
        $sector = Setor::create(['nome' => 'Administrativo']);
        $data = ['empresa_id' => $company->id, 'setor_id' => $sector->id,
            'descricao' => 'Manutenção', 'vai_almocar' => '0', 'status' => 'Pendente',
            'data_servico' => '2026-09-17'];
        $this->get('/servicos/create')->assertOk();
        $this->post('/servicos', $data)->assertSessionHasNoErrors();
        $service = Servico::firstOrFail();
        $this->get('/servicos/' . $service->id)->assertOk();
        $this->get('/servicos/' . $service->id . '/edit')->assertOk();
        $data['status'] = 'Finalizado';
        $data['data_conclusao'] = '2026-09-16';
        $this->put('/servicos/' . $service->id, $data)->assertSessionHasErrors('data_conclusao');
        $data['data_conclusao'] = '2026-09-18';
        $this->put('/servicos/' . $service->id, $data)->assertSessionHasNoErrors();
        $this->assertSame('Finalizado', $service->fresh()->status);
        $this->delete('/servicos/' . $service->id)->assertRedirect('/servicos');
    }

    public function test_new_service_request_always_starts_pending()
    {
        $this->actingAs($this->user());
        $company = Empresa::create($this->companyData());
        $sector = Setor::create(['nome' => 'Portaria']);

        $this->post('/servicos', [
            'empresa_id' => $company->id,
            'setor_id' => $sector->id,
            'descricao' => 'Acesso para manutenção preventiva',
            'vai_almocar' => '0',
            'status' => 'Finalizado',
            'data_servico' => now()->format('Y-m-d'),
            'data_conclusao' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $service = Servico::firstOrFail();
        $this->assertSame('Pendente', $service->status);
        $this->assertNull($service->data_conclusao);
    }

    public function test_documents_require_authentication_and_parent_ownership()
    {
        Storage::fake('public');
        $admin = $this->user();
        $data = $this->companyData();
        $data['documentos'] = [UploadedFile::fake()->create('contrato.pdf', 20, 'application/pdf')];
        $this->actingAs($admin)->post('/empresas', $data)->assertSessionHasNoErrors();
        $doc = Documento::firstOrFail();
        $url = '/empresas/' . $doc->empresa_id . '/documentos/' . $doc->id;
        Storage::disk('public')->assertExists($doc->caminho_arquivo);
        $this->get($url)->assertOk();
        $other = $this->companyData();
        $other['cnpj'] = '98765432000190';
        $other = Empresa::create($other);
        $this->get('/empresas/' . $other->id . '/documentos/' . $doc->id)->assertNotFound();
        $this->post('/logout');
        $this->get($url)->assertRedirect('/login');
        $this->actingAs($admin)->delete('/empresas/' . $doc->empresa_id);
        Storage::disk('public')->assertMissing($doc->caminho_arquivo);
    }

    public function test_admin_pages_and_duplicate_email_validation()
    {
        $this->actingAs($this->user());
        $this->get('/usuarios')->assertOk();
        $this->get('/usuarios/create')->assertOk();
        $data = ['name' => 'Operador', 'username' => 'operador', 'setor' => 'RH',
            'email' => 'operador@example.test', 'password' => 'SenhaTeste123!',
            'password_confirmation' => 'SenhaTeste123!', 'permissao' => 'Usuário'];
        $this->post('/usuarios', $data)->assertSessionHasNoErrors();
        $user = Usuario::where('username', 'operador')->firstOrFail();
        $this->get('/usuarios/' . $user->id)->assertOk();
        $this->get('/usuarios/' . $user->id . '/edit')->assertOk();
        $data['username'] = 'outro';
        $this->post('/usuarios', $data)->assertSessionHasErrors('email');
    }

    public function test_initial_seed_is_repeatable_and_preserves_passwords()
    {
        $user = $this->user();
        $passwordHash = $user->password;
        $this->seed(\Database\Seeders\DockerSeeder::class);
        $this->seed(\Database\Seeders\DockerSeeder::class);
        $this->assertSame(8, Setor::count());
        $this->assertSame(1, Usuario::count());
        $this->assertSame($passwordHash, $user->fresh()->password);
    }

    public function test_document_has_fixed_six_month_validity_and_can_be_reviewed()
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-09-17 10:00:00');
        $admin = $this->user();
        $company = Empresa::create($this->companyData());
        $this->actingAs($admin)->post('/empresas/' . $company->id . '/documentos', [
            'arquivo' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $document = Documento::latest('id')->firstOrFail();
        $this->assertSame('Pendente', $document->status);
        $this->assertSame('2027-03-17', $document->validade_ate->toDateString());

        $this->put('/empresas/' . $company->id . '/documentos/' . $document->id . '/analise', [
            'status' => 'Aprovado',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Aprovado', $document->fresh()->status);
        $this->assertSame($admin->id, $document->fresh()->analisado_por);
        Carbon::setTestNow();
    }

    public function test_admin_controls_document_job()
    {
        $this->actingAs($this->user());
        $this->get('/configuracoes')->assertOk();
        $this->put('/configuracoes/job-documentos', ['ativo' => '0'])->assertSessionHasNoErrors();
        $this->assertSame('0', Configuracao::valor('job_validade_documentos_ativo'));
    }

    public function test_only_admin_or_work_safety_can_manage_company_documents()
    {
        Storage::fake('public');
        $company = Empresa::create($this->companyData());
        $regular = $this->user('Usuário');
        $this->actingAs($regular)->post('/empresas/' . $company->id . '/documentos', [
            'arquivo' => UploadedFile::fake()->create('certidao.pdf', 20, 'application/pdf'),
        ])->assertForbidden();

        $safety = $this->user('Usuário');
        $safety->update(['setor' => 'Segurança do Trabalho']);
        $this->actingAs($safety)->post('/empresas/' . $company->id . '/documentos', [
            'arquivo' => UploadedFile::fake()->create('certidao.pdf', 20, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, Documento::count());
    }

    public function test_approved_service_allows_any_regular_employee_from_its_company()
    {
        Carbon::setTestNow('2026-09-17 09:00:00');
        $requester = $this->user();
        $company = Empresa::create($this->companyData());
        $employee = Funcionario::create([
            'empresa_id' => $company->id, 'nome' => 'Terceiro Regular',
            'cpf' => '12345678901', 'ativo' => true,
        ]);
        Documento::create([
            'empresa_id' => $company->id, 'nome_arquivo' => 'empresa.pdf',
            'caminho_arquivo' => 'empresa.pdf', 'status' => 'Aprovado',
        ]);
        DocumentoFuncionario::create([
            'funcionario_id' => $employee->id, 'nome_original' => 'funcionario.pdf',
            'path' => 'funcionario.pdf', 'status' => 'Aprovado',
        ]);
        $sector = Setor::create(['nome' => 'Manutenção']);
        $service = Servico::create([
            'empresa_id' => $company->id, 'solicitante_id' => $requester->id,
            'setor_id' => $sector->id, 'descricao' => 'Serviço autorizado',
            'vai_almocar' => false, 'status' => 'Aprovado', 'data_servico' => today(),
        ]);

        $this->assertTrue($service->autorizaFuncionario($employee));
        $employee->update(['ativo' => false]);
        $this->assertFalse($service->fresh()->autorizaFuncionario($employee->fresh()));
        Carbon::setTestNow();
    }
}
