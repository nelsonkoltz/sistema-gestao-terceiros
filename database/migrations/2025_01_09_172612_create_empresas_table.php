<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresasTable extends Migration
{
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj');
            $table->string('telefone');
            $table->string('email');
            $table->string('endereco_rua');
            $table->string('endereco_numero');
            $table->string('endereco_bairro');
            $table->string('endereco_cidade');
            $table->string('endereco_estado');
            $table->string('endereco_cep');
            $table->timestamps();
        });
    }
    
}
