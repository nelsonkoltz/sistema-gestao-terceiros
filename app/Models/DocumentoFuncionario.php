<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoFuncionario extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'funcionario_documentos';

    // Campos permitidos para inserção em massa
    protected $fillable = [
        'funcionario_id',
        'nome_original',
        'path',
        'mime',
        'tamanho',
    ];

    // Tipos de dados (conversão automática)
    protected $casts = [
        'tamanho' => 'integer',
    ];

    // Relação: cada documento pertence a um funcionário
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
