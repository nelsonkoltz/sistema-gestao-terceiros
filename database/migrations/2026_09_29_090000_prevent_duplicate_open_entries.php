<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $duplicados = DB::table('registros_acesso')->whereNotNull('entrada_em')->whereNull('saida_em')
            ->groupBy('funcionario_id')->havingRaw('COUNT(*) > 1')->pluck('funcionario_id');
        if ($duplicados->isNotEmpty()) {
            throw new RuntimeException('Existem entradas abertas duplicadas. Revise os funcionários antes de migrar: '.$duplicados->implode(', '));
        }
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->unsignedBigInteger('funcionario_presente_id')->nullable()
                ->storedAs('CASE WHEN entrada_em IS NOT NULL AND saida_em IS NULL THEN funcionario_id ELSE NULL END');
            $table->unique('funcionario_presente_id', 'uma_entrada_aberta_por_funcionario');
        });
    }

    public function down(): void
    {
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->dropUnique('uma_entrada_aberta_por_funcionario');
            $table->dropColumn('funcionario_presente_id');
        });
    }
};
