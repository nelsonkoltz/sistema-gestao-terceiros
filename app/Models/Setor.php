<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'setores';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = ['nome'];

    /**
     * Relacionamento: um setor pode ter vários serviços
     */
    public function servicos()
    {
        return $this->hasMany(Servico::class, 'setor_id');
    }
}
