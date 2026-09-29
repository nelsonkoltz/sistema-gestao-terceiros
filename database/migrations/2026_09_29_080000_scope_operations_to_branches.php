<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $matriz = DB::table('filiais')->where('codigo', 'MATRIZ')->value('id');
        if (!$matriz) throw new RuntimeException('A filial MATRIZ deve existir para migrar o histórico.');

        foreach (['servicos', 'registros_acesso'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($matriz) {
                $table->foreignId('filial_id')->default($matriz)->constrained('filiais')->restrictOnDelete();
            });
        }
        Schema::table('servicos', function (Blueprint $table) {
            $table->index(['filial_id', 'data_servico', 'status']);
        });
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->index(['filial_id', 'entrada_em', 'saida_em']);
        });
    }

    public function down(): void
    {
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->dropIndex(['filial_id', 'entrada_em', 'saida_em']);
            $table->dropConstrainedForeignId('filial_id');
        });
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropIndex(['filial_id', 'data_servico', 'status']);
            $table->dropConstrainedForeignId('filial_id');
        });
    }
};
