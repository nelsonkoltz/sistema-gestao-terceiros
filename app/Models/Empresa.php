<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'cnpj', 'telefone', 'email',
        'endereco_rua', 'endereco_numero', 'endereco_bairro',
        'endereco_cidade', 'endereco_estado', 'endereco_cep',
        'tipo',  // Adicionado o tipo para pessoa jurídica ou física
    ];

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'empresa_id');
    }

    public function funcionarios()
{
    return $this->hasMany(Funcionario::class);
}

}
