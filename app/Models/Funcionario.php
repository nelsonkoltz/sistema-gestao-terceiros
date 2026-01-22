<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $fillable = ['empresa_id', 'nome', 'cpf', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // salva cpf sem máscara
    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/\D/', '', (string) $value) ?? $value;
    }

    // acessor útil para exibir formatado: {{ $funcionario->cpf_formatado }}
    public function getCpfFormatadoAttribute()
    {
        $cpf = $this->cpf;
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoFuncionario::class, 'funcionario_id');
    }

}
