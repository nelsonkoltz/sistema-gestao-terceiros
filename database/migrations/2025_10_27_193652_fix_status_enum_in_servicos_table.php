<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            // 🔹 Remove a coluna antiga apenas se ela existir
            if (Schema::hasColumn('servicos', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('servicos', function (Blueprint $table) {
            // 🔹 Cria novamente o campo como VARCHAR em vez de ENUM
            $table->string('status', 50)->default('Pendente')->after('vai_almocar');
        });

        // 🔹 Corrige valores antigos, caso existam
        DB::table('servicos')
            ->whereNull('status')
            ->update(['status' => 'Pendente']);
    }

    public function down(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
