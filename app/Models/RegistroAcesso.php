<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAcesso extends Model
{
    protected $table = 'registros_acesso';
    protected $fillable = ['funcionario_id', 'servico_id', 'registrado_por', 'decisao', 'motivo', 'entrada_em', 'saida_em'];
    protected $casts = ['entrada_em' => 'datetime', 'saida_em' => 'datetime'];

    public function funcionario() { return $this->belongsTo(Funcionario::class); }
    public function servico() { return $this->belongsTo(Servico::class); }
    public function operador() { return $this->belongsTo(Usuario::class, 'registrado_por'); }
}
