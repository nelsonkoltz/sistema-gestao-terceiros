<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_documento_id');
        });
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_documento_id');
        });
        Schema::dropIfExists('tipos_documento');
    }

    public function down(): void
    {
        Schema::create('tipos_documento', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('aplicacao', 20)->default('ambos');
            $table->boolean('obrigatorio')->default(true);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('tipo_documento_id')->nullable()->constrained('tipos_documento')->nullOnDelete();
        });
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->foreignId('tipo_documento_id')->nullable()->constrained('tipos_documento')->nullOnDelete();
        });
    }
};
