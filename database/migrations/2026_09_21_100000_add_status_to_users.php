<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('ativo')->default(true)->after('permissao');
            $table->text('motivo_inativacao')->nullable()->after('ativo');
            $table->foreignId('inativado_por_id')->nullable()->after('motivo_inativacao')->constrained('usuarios')->nullOnDelete();
            $table->timestamp('inativado_em')->nullable()->after('inativado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['inativado_por_id']);
            $table->dropColumn(['ativo', 'motivo_inativacao', 'inativado_por_id', 'inativado_em']);
        });
    }
};
