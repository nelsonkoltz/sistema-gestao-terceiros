<?php

namespace Tests\Feature;

use App\Models\{Documento, DocumentoFuncionario, Empresa, Filial, Funcionario, RegistroAcesso, Servico, Setor, Usuario};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class GateConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('terceiroscr_test', DB::connection()->getDatabaseName(), 'Use o Compose de testes isolados.');
        $this->artisan('migrate:fresh', ['--force' => true])->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        if (DB::connection()->getDatabaseName() === 'terceiroscr_test') {
            $this->artisan('db:wipe', ['--force' => true]);
        }
        RefreshDatabaseState::$migrated = false;
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_simultaneous_requests_create_only_one_open_entry(): void
    {
        $this->assertSame('terceiroscr_test', DB::connection()->getDatabaseName(), 'Use o Compose de testes isolados.');
        Carbon::setTestNow('2026-09-29 10:00:00');
        $user = Usuario::create(['name' => 'Guarita', 'username' => 'concorrencia', 'permissao' => 'Guarita',
            'ativo' => true, 'password' => Hash::make('SenhaTeste123!')]);
        $user->filiais()->attach(Filial::first()->id, ['principal' => true]);
        $company = Empresa::create(['nome' => 'Empresa', 'cnpj' => '12345678000190', 'tipo' => 'CNPJ',
            'telefone' => '47999999999', 'email' => 'empresa@example.test', 'endereco_rua' => 'Rua Teste',
            'endereco_numero' => '1', 'endereco_bairro' => 'Centro', 'endereco_cidade' => 'Blumenau',
            'endereco_cep' => '89000000', 'endereco_estado' => 'SC', 'ativo' => true]);
        $employee = Funcionario::create(['nome' => 'Pessoa', 'cpf' => '12345678901', 'empresa_id' => $company->id, 'ativo' => true]);
        Documento::create(['empresa_id' => $company->id, 'nome_arquivo' => 'empresa.pdf', 'caminho_arquivo' => 'empresa.pdf']);
        DocumentoFuncionario::create(['funcionario_id' => $employee->id, 'nome_original' => 'pessoa.pdf', 'path' => 'pessoa.pdf']);
        $sector = Setor::create(['nome' => 'TI']);
        $service = Servico::create(['empresa_id' => $company->id, 'solicitante_id' => $user->id,
            'setor_id' => $sector->id, 'descricao' => 'Concorrência', 'vai_almocar' => false, 'status' => 'Agendado',
            'data_servico' => today(), 'hora_inicio' => '08:00:00', 'hora_fim' => '18:00:00']);
        $prefix = sys_get_temp_dir().'/gate-'.bin2hex(random_bytes(8));
        $processes = [];
        try {
            DB::beginTransaction();
            Funcionario::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            for ($i = 0; $i < 2; $i++) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/gate-request.php'),
                    (string) $user->id, (string) $employee->id, $prefix.'-'.$i, $prefix.'-go'], base_path(),
                    ['SESSION_DRIVER' => 'array', 'CACHE_DRIVER' => 'array', 'MAIL_MAILER' => 'array']);
                $process->setTimeout(25)->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 15;
            while (!is_file($prefix.'-0') || !is_file($prefix.'-1')) {
                $this->assertLessThan($deadline, microtime(true), 'Os workers não iniciaram.');
                usleep(10000);
            }
            file_put_contents($prefix.'-go', 'go');
            usleep(250000);
            DB::commit();
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $results[] = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            }
            $this->assertSame([302, 302], array_column($results, 'status'));
            $this->assertCount(1, array_filter(array_column($results, 'success')));
            $this->assertCount(1, array_filter(array_column($results, 'error')));
            $this->assertSame(1, RegistroAcesso::where('funcionario_id', $employee->id)->whereNotNull('entrada_em')->whereNull('saida_em')->count());
            $this->assertSame('Em Andamento', $service->fresh()->status);
        } finally {
            if (DB::transactionLevel()) DB::rollBack();
            foreach ($processes as $process) if ($process->isRunning()) $process->stop();
            foreach ([$prefix.'-0', $prefix.'-1', $prefix.'-go'] as $file) if (is_file($file)) unlink($file);
            Carbon::setTestNow();
        }
    }
}
