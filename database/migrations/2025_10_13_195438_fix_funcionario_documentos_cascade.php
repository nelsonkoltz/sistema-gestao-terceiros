<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->dropForeign(['funcionario_id']);

            // ✅ funcionário apagado -> documentos também
            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->dropForeign(['funcionario_id']);

            // rollback para restrict (caso precise reverter)
            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }
};
