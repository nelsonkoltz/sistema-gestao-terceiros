<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->text('motivo_cancelamento')->nullable()->after('data_conclusao');
            $table->foreignId('cancelado_por_id')->nullable()->after('motivo_cancelamento')->constrained('usuarios')->nullOnDelete();
            $table->timestamp('cancelado_em')->nullable()->after('cancelado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropForeign(['cancelado_por_id']);
            $table->dropColumn(['motivo_cancelamento', 'cancelado_por_id', 'cancelado_em']);
        });
    }
};
