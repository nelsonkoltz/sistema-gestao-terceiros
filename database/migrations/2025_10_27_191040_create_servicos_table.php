<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            
            // Relacionamento com empresa
            $table->foreignId('empresa_id')
                  ->constrained('empresas')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            // Relacionamento com usuário solicitante
            $table->foreignId('solicitante_id')
                ->constrained('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            // Campo se o terceiro vai almoçar
            $table->boolean('vai_almocar')->default(false);

            // Status do serviço
            $table->enum('status', ['Ativo', 'Inativo'])->default('Ativo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
