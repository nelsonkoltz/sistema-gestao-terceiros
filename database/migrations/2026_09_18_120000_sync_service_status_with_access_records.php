<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SyncServiceStatusWithAccessRecords extends Migration
{
    public function up()
    {
        $comEntradaAberta = DB::table('registros_acesso')
            ->whereNotNull('servico_id')
            ->whereNotNull('entrada_em')
            ->whereNull('saida_em')
            ->distinct()
            ->pluck('servico_id');

        if ($comEntradaAberta->isNotEmpty()) {
            DB::table('servicos')
                ->whereIn('id', $comEntradaAberta)
                ->update(['status' => 'Em Andamento', 'data_conclusao' => null]);
        }

        $comAcessoEncerrado = DB::table('registros_acesso')
            ->whereNotNull('servico_id')
            ->whereNotNull('entrada_em')
            ->whereNotNull('saida_em')
            ->whereNotIn('servico_id', $comEntradaAberta)
            ->distinct()
            ->pluck('servico_id');

        if ($comAcessoEncerrado->isNotEmpty()) {
            DB::table('servicos')
                ->whereIn('id', $comAcessoEncerrado)
                ->update(['status' => 'Finalizado', 'data_conclusao' => today()->toDateString()]);
        }
    }

    public function down()
    {
        // A situação anterior não pode ser reconstruída com segurança.
    }
}
