<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'servicos';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'empresa_id',
        'solicitante_id',
        'descricao',
        'vai_almocar',
        'status',
        'setor_id',
        'data_servico',
        'data_conclusao',
    ];

    /**
     * Casts automáticos
     */
    protected $casts = [
        'vai_almocar' => 'boolean',
        'data_servico' => 'date',
        'data_conclusao' => 'date',
    ];

    /**
     * Regra de negócio:
     * Se o serviço for finalizado e não tiver data de conclusão,
     * define automaticamente a data atual.
     */
    protected static function booted()
    {
        static::saving(function ($servico) {
            if (
                $servico->status === 'Finalizado' &&
                empty($servico->data_conclusao)
            ) {
                $servico->data_conclusao = now()->format('Y-m-d');
            }
        });
    }

    /**
     * Relacionamento: Serviço pertence a uma Empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento: Serviço foi solicitado por um Usuário
     */
    public function solicitante()
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }

    /**
     * Relacionamento: Serviço pertence a um Setor
     */
    public function setor()
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }

    public function autorizaFuncionario(Funcionario $funcionario): bool
    {
        return (int) $funcionario->empresa_id === (int) $this->empresa_id
            && in_array($this->status, ['Aprovado', 'Em Andamento'], true)
            && $this->data_servico?->isSameDay(today())
            && $this->empresa->documentacaoRegular()
            && $funcionario->documentacaoRegular();
    }
}
