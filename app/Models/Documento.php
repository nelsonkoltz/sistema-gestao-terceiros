<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Adicione esta linha

class Documento extends Model
{
    use HasFactory, SoftDeletes; // Inclua a trait SoftDeletes

    protected $fillable = ['empresa_id', 'nome_arquivo', 'caminho_arquivo', 'status',
        'validade_ate', 'observacao_analise', 'analisado_por', 'analisado_em', 'documento_anterior_id'];

    protected $casts = ['validade_ate' => 'date', 'analisado_em' => 'datetime'];

    protected static function booted()
    {
        static::creating(function ($documento) {
            $documento->status = $documento->status ?: 'Pendente';
            $documento->validade_ate = $documento->validade_ate ?: now()->addMonthsNoOverflow(6)->toDateString();
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
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
