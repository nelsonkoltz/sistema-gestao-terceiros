<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Adicione esta linha
use App\Models\Concerns\Auditable;

class Documento extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ['empresa_id', 'nome_arquivo', 'caminho_arquivo', 'status',
        'validade_ate', 'observacao_analise', 'analisado_por', 'analisado_em', 'documento_anterior_id'];

    protected $casts = ['validade_ate' => 'date', 'analisado_em' => 'datetime'];

    protected static function booted()
    {
        static::creating(function ($documento) {
            $documento->status = $documento->status ?: 'Ativo';
            $unidade = Configuracao::valor('validade_documentos_unidade', 'meses');
            $quantidade = max(1, (int) Configuracao::valor('validade_documentos_quantidade', Configuracao::valor('validade_documentos_meses', 6)));
            $validade = $unidade === 'dias' ? now()->addDays($quantidade) : now()->addMonthsNoOverflow($quantidade);
            $documento->validade_ate = $documento->validade_ate ?: $validade->toDateString();
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function analisador() { return $this->belongsTo(Usuario::class, 'analisado_por'); }
    public function anterior() { return $this->belongsTo(self::class, 'documento_anterior_id'); }
    public function posteriores() { return $this->hasMany(self::class, 'documento_anterior_id'); }

    public function fazParteDoHistorico(): bool
    {
        return (bool) $this->documento_anterior_id
            || $this->status === 'Substituido'
            || $this->posteriores()->withTrashed()->exists();
    }

    public function getStatusAtualAttribute(): string
    {
        if ($this->status !== 'Substituido' && $this->validade_ate?->lte(today())) return 'Vencido';
        if ($this->status !== 'Substituido' && $this->validade_ate?->lte(today()->addDays(30))) return 'Proximo do vencimento';
        return $this->status;
    }
}
