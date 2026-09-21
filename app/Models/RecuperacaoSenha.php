<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecuperacaoSenha extends Model
{
    protected $table = 'recuperacoes_senha';
    protected $guarded = [];
    protected $casts = ['expira_em' => 'datetime', 'utilizado_em' => 'datetime'];
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
