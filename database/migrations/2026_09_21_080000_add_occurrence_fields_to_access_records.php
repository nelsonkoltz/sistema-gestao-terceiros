<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->string('tipo_registro', 20)->default('Acesso')->after('decisao');
            $table->string('categoria', 40)->nullable()->after('tipo_registro');
            $table->text('observacao')->nullable()->after('motivo');
            $table->text('observacao_saida')->nullable()->after('observacao');
            $table->string('endereco_ip', 45)->nullable()->after('registrado_por');
            $table->index(['tipo_registro', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->dropIndex(['tipo_registro', 'created_at']);
            $table->dropColumn(['tipo_registro', 'categoria', 'observacao', 'observacao_saida', 'endereco_ip']);
        });
    }
};
