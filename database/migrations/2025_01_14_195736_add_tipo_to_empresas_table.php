<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoToEmpresasTable extends Migration
{
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->enum('tipo', ['CNPJ', 'CPF'])->after('cnpj'); // Adiciona o campo tipo, com CNPJ ou CPF como valores possíveis
        });
    }
    
    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('tipo'); // Caso seja necessário reverter a migration
        });
    }
    
}
