<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveManualApprovals extends Migration
{
    public function up()
    {
        DB::table('servicos')
            ->whereIn('status', ['Pendente', 'Aprovado'])
            ->update(['status' => 'Agendado']);

        DB::table('documentos')
            ->whereIn('status', ['Pendente', 'Aprovado', 'Rejeitado', 'Proximo do vencimento'])
            ->update(['status' => 'Ativo']);

        DB::table('funcionario_documentos')
            ->whereIn('status', ['Pendente', 'Aprovado', 'Rejeitado', 'Proximo do vencimento'])
            ->update(['status' => 'Ativo']);
    }

    public function down()
    {
        DB::table('servicos')->where('status', 'Agendado')->update(['status' => 'Pendente']);
        DB::table('documentos')->where('status', 'Ativo')->update(['status' => 'Pendente']);
        DB::table('funcionario_documentos')->where('status', 'Ativo')->update(['status' => 'Pendente']);
    }
}
