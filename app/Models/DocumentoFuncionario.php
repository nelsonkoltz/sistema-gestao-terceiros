<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class DocumentoFuncionario extends Model
{
    use HasFactory, Auditable;

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
            $documento->status = $documento->status ?: 'Ativo';
            $unidade = Configuracao::valor('validade_documentos_unidade', 'meses');
            $quantidade = max(1, (int) Configuracao::valor('validade_documentos_quantidade', Configuracao::valor('validade_documentos_meses', 6)));
            $validade = $unidade === 'dias' ? now()->addDays($quantidade) : now()->addMonthsNoOverflow($quantidade);
            $documento->validade_ate = $documento->validade_ate ?: $validade->toDateString();
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
        if ($this->status !== 'Substituido' && $this->validade_ate?->lte(today())) return 'Vencido';
        if ($this->status !== 'Substituido' && $this->validade_ate?->lte(today()->addDays(30))) return 'Proximo do vencimento';
        return $this->status;
    }
}
