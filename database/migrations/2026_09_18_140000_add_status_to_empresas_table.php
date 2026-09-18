<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToEmpresasTable extends Migration
{
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('ativo')->default(true)->after('endereco_cep');
            $table->text('motivo_inativacao')->nullable()->after('ativo');
            $table->unsignedBigInteger('inativada_por_id')->nullable()->after('motivo_inativacao');
            $table->timestamp('inativada_em')->nullable()->after('inativada_por_id');
            $table->foreign('inativada_por_id')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropForeign(['inativada_por_id']);
            $table->dropColumn(['ativo', 'motivo_inativacao', 'inativada_por_id', 'inativada_em']);
        });
    }
}
