<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Adicione esta linha

class Documento extends Model
{
    use HasFactory, SoftDeletes; // Inclua a trait SoftDeletes

    protected $fillable = ['empresa_id', 'nome_arquivo', 'caminho_arquivo'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
