<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToDocumentosTable extends Migration
{
    public function up()
    {
        // Adiciona a coluna 'deleted_at' para soft deletes
        Schema::table('documentos', function (Blueprint $table) {
            $table->softDeletes();  // Adiciona a coluna 'deleted_at'
        });
    }

    public function down()
    {
        // Se necessário, remove a coluna 'deleted_at'
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
