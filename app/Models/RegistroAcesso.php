<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class RegistroAcesso extends Model
{
    use Auditable;

    protected $table = 'registros_acesso';
    protected $fillable = ['funcionario_id', 'servico_id', 'registrado_por', 'endereco_ip', 'decisao', 'tipo_registro', 'categoria', 'motivo', 'observacao', 'observacao_saida', 'entrada_em', 'saida_em'];
    protected $casts = ['entrada_em' => 'datetime', 'saida_em' => 'datetime'];

    public function funcionario() { return $this->belongsTo(Funcionario::class); }
    public function servico() { return $this->belongsTo(Servico::class); }
    public function operador() { return $this->belongsTo(Usuario::class, 'registrado_por'); }
}
