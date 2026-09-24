<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFiliaisAndLinkUsers extends Migration
{
    public function up()
    {
        Schema::create('filiais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo', 30)->unique();
            $table->string('cnpj', 14)->nullable()->unique();
            $table->string('telefone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });

        Schema::create('filial_usuario', function (Blueprint $table) {
            $table->foreignId('filial_id')->constrained('filiais')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->boolean('principal')->default(false);
            $table->timestamps();
            $table->primary(['filial_id', 'usuario_id']);
            $table->index(['usuario_id', 'principal']);
        });

        $agora = now();
        $filialId = DB::table('filiais')->insertGetId([
            'nome' => 'Matriz',
            'codigo' => 'MATRIZ',
            'ativa' => true,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        DB::table('usuarios')->orderBy('id')->each(function ($usuario) use ($filialId, $agora) {
            DB::table('filial_usuario')->insert([
                'filial_id' => $filialId,
                'usuario_id' => $usuario->id,
                'principal' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('filial_usuario');
        Schema::dropIfExists('filiais');
    }
}
