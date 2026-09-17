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
        'status',
        'validade_ate',
        'observacao_analise',
        'analisado_por',
        'analisado_em',
        'documento_anterior_id',
    ];

    // Tipos de dados (conversão automática)
    protected $casts = [
        'tamanho' => 'integer',
        'validade_ate' => 'date',
        'analisado_em' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($documento) {
            $documento->status = $documento->status ?: 'Pendente';
            $documento->validade_ate = $documento->validade_ate ?: now()->addMonthsNoOverflow(6)->toDateString();
        });
    }

    // Relação: cada documento pertence a um funcionário
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function analisador() { return $this->belongsTo(Usuario::class, 'analisado_por'); }
    public function anterior() { return $this->belongsTo(self::class, 'documento_anterior_id'); }

    public function getStatusAtualAttribute(): string
    {
        if (!in_array($this->status, ['Rejeitado', 'Pendente'], true) && $this->validade_ate?->lt(today())) return 'Vencido';
        if ($this->status === 'Aprovado' && $this->validade_ate?->lte(today()->addDays(30))) return 'Proximo do vencimento';
        return $this->status;
    }
}
