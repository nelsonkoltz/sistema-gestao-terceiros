<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE usuarios MODIFY permissao VARCHAR(50) NOT NULL DEFAULT 'Solicitante'");
        }
        DB::table('usuarios')->where('permissao', 'Usuário')->update(['permissao' => 'Solicitante']);
        DB::table('usuarios')->where('permissao', 'Consulta')->update(['permissao' => 'Guarita']);

        Schema::table('servicos', function (Blueprint $table) {
            $table->time('hora_inicio')->nullable()->after('data_servico');
            $table->time('hora_fim')->nullable()->after('hora_inicio');
        });

        Schema::create('registros_acesso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->restrictOnDelete();
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete();
            $table->string('decisao', 20);
            $table->text('motivo')->nullable();
            $table->timestamp('entrada_em')->nullable();
            $table->timestamp('saida_em')->nullable();
            $table->timestamps();
            $table->index(['funcionario_id', 'saida_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_acesso');
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fim']);
        });
        DB::table('usuarios')->where('permissao', 'Solicitante')->update(['permissao' => 'Usuário']);
        DB::table('usuarios')->where('permissao', 'Guarita')->update(['permissao' => 'Consulta']);
    }
};
