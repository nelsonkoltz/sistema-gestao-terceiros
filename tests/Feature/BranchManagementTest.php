<?php

namespace Tests\Feature;

use App\Models\Filial;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'Administrador'): Usuario
    {
        return Usuario::create([
            'name' => 'Teste Filial', 'username' => 'filial-'.uniqid(),
            'email' => uniqid().'@example.test', 'setor' => 'TI',
            'password' => Hash::make('SenhaTeste123!'), 'permissao' => $role, 'ativo' => true,
        ]);
    }

    public function test_migration_creates_matrix_branch(): void
    {
        $this->assertDatabaseHas('filiais', ['nome' => 'Matriz', 'codigo' => 'MATRIZ', 'ativa' => true]);
    }

    public function test_admin_can_manage_branches(): void
    {
        $admin = $this->user();
        $this->actingAs($admin)->get('/filiais')->assertOk();
        $this->post('/filiais', [
            'nome' => 'Filial Norte', 'codigo' => 'norte', 'cnpj' => '12345678000190',
            'cidade' => 'Blumenau', 'estado' => 'sc', 'ativa' => '1',
        ])->assertRedirect('/filiais');

        $filial = Filial::where('codigo', 'NORTE')->firstOrFail();
        $this->get('/filiais/'.$filial->id)->assertOk()->assertSee('Filial Norte');
        $this->put('/filiais/'.$filial->id, [
            'nome' => 'Filial Norte Atualizada', 'codigo' => 'NORTE',
            'cidade' => 'Blumenau', 'estado' => 'SC', 'ativa' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($filial->fresh()->ativa);
    }

    public function test_user_can_be_linked_to_multiple_branches_with_one_primary(): void
    {
        $admin = $this->user();
        $matriz = Filial::where('codigo', 'MATRIZ')->firstOrFail();
        $segunda = Filial::create(['nome' => 'Filial Sul', 'codigo' => 'SUL', 'ativa' => true]);

        $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Usuário Duas Filiais', 'setor' => 'Compras', 'username' => 'duas-filiais',
            'email' => 'duas@example.test', 'password' => 'SenhaNova123!',
            'password_confirmation' => 'SenhaNova123!', 'permissao' => 'Solicitante', 'ativo' => '1',
            'filiais' => [$matriz->id, $segunda->id], 'filial_principal_id' => $segunda->id,
        ])->assertSessionHasNoErrors();

        $usuario = Usuario::where('username', 'duas-filiais')->firstOrFail();
        $this->assertCount(2, $usuario->filiais);
        $this->assertDatabaseHas('filial_usuario', ['usuario_id' => $usuario->id, 'filial_id' => $segunda->id, 'principal' => true]);
        $this->assertDatabaseHas('filial_usuario', ['usuario_id' => $usuario->id, 'filial_id' => $matriz->id, 'principal' => false]);
    }

    public function test_non_admin_cannot_access_branch_management(): void
    {
        $this->actingAs($this->user('Solicitante'))->get('/filiais')->assertForbidden();
    }
}
