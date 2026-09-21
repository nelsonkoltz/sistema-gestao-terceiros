<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('trocar_senha')->default(false)->after('password');
            $table->timestamp('senha_alterada_em')->nullable()->after('trocar_senha');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', fn (Blueprint $table) => $table->dropColumn(['trocar_senha', 'senha_alterada_em']));
    }
};
