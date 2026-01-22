<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->foreignId('setor_id')->nullable()->constrained('setores')->nullOnDelete();
            $table->date('data_servico')->nullable();
            $table->date('data_conclusao')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropForeign(['setor_id']);
            $table->dropColumn(['setor_id', 'data_servico', 'data_conclusao']);
        });
    }
};
