<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recuperacoes_senha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expira_em');
            $table->timestamp('utilizado_em')->nullable();
            $table->string('ip_solicitacao', 45)->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'expira_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recuperacoes_senha');
    }
};
