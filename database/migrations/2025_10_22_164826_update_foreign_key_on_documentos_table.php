<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Remove a FK antiga
            $table->dropForeign(['empresa_id']);

            // Cria novamente com CASCADE
            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->onDelete('cascade')   // ✅ apaga documentos junto com empresa
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Reverte para o comportamento anterior
            $table->dropForeign(['empresa_id']);
            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }
};
