<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo',
        'cnpj',
        'telefone',
        'email',
        'endereco_rua',
        'endereco_numero',
        'endereco_bairro',
        'endereco_cidade',
        'endereco_estado',
        'endereco_cep',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($empresa) {
            $empresa->cnpj = preg_replace('/\D/', '', $empresa->cnpj);
            $empresa->endereco_estado = strtoupper(substr($empresa->endereco_estado, 0, 2));
        });
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class);
    }

    public function getCnpjFormatadoAttribute()
    {
        if (strlen($this->cnpj) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->cnpj);
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->cnpj);
    }

    public function getTelefoneFormatadoAttribute()
    {
        $telefone = preg_replace('/\D/', '', (string) $this->telefone);

        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        }

        if (strlen($telefone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }

        return $this->telefone;
    }

    public function getCepFormatadoAttribute()
    {
        $cep = preg_replace('/\D/', '', (string) $this->endereco_cep);
        return strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $this->endereco_cep;
    }
}
