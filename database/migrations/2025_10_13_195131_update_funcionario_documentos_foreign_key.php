<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            // Remove a foreign key antiga (com cascade)
            $table->dropForeign(['funcionario_id']);

            // Cria novamente sem cascade para evitar excluir funcionário junto
            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('restrict')   // impede apagar funcionário ao deletar documento
                ->onUpdate('cascade');   // mantém atualização normal
        });
    }

    public function down(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            // Reverte a mudança
            $table->dropForeign(['funcionario_id']);

            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
