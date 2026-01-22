<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetorSeeder extends Seeder
{
    public function run(): void
    {
        $setores = [
            'Fiação', 'Expedição', 'Tecelagem', 'Tinturaria',
            'Caldeira', 'ETE/ETA', 'RH', 'Administrativo'
        ];

        foreach ($setores as $nome) {
            DB::table('setores')->insert(['nome' => $nome]);
        }
    }
}
