<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            // Remove a FK antiga, se existir
            $table->dropForeign(['funcionario_id']);

            // Recria a FK com ON DELETE CASCADE
            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('cascade'); // <-- apaga documentos quando o funcionário é excluído
        });
    }

    public function down(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->dropForeign(['funcionario_id']);

            // Restaura sem o cascade (se quiser reverter)
            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('restrict'); // impede exclusão se houver documentos
        });
    }
};
