<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('servicos', function (Blueprint $table) {
        if (!Schema::hasColumn('servicos', 'descricao')) {
            $table->text('descricao')->after('id');
        }

        if (!Schema::hasColumn('servicos', 'status')) {
            $table->enum('status', ['Pendente', 'Em Andamento', 'Finalizado'])
                  ->default('Pendente')->after('vai_almocar');
        }
    });
}

public function down(): void
{
    Schema::table('servicos', function (Blueprint $table) {
        $table->dropColumn(['descricao', 'status']);
    });
}

};
