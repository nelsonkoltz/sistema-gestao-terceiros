<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditoriasTable extends Migration
{
    public function up()
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('modulo', 80);
            $table->string('acao', 30);
            $table->string('registro_tipo')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['modulo', 'acao']);
            $table->index(['registro_tipo', 'registro_id']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditorias');
    }
}
