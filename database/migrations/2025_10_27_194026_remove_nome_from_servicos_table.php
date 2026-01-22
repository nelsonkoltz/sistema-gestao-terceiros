<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            if (Schema::hasColumn('servicos', 'nome')) {
                $table->dropColumn('nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->string('nome')->nullable();
        });
    }
};
