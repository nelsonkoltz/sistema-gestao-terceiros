<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Servico;
use App\Models\Setor;
use App\Models\Usuario;
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
}
