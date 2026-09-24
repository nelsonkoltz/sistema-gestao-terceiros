<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    use Auditable;

    protected $table = 'filiais';
    protected $fillable = ['nome', 'codigo', 'cnpj', 'telefone', 'email', 'endereco', 'cidade', 'estado', 'ativa'];
    protected $casts = ['ativa' => 'boolean'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'filial_usuario')
            ->withPivot('principal')->withTimestamps();
    }
}
