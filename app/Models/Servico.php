<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class Servico extends Model
{
    use HasFactory, Auditable;

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
        'hora_inicio',
        'hora_fim',
        'data_conclusao',
        'motivo_cancelamento',
        'cancelado_por_id',
        'cancelado_em',
    ];

    /**
     * Casts automáticos
     */
    protected $casts = [
        'vai_almocar' => 'boolean',
        'data_servico' => 'date',
        'data_conclusao' => 'date',
        'cancelado_em' => 'datetime',
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

    public function registrosAcesso()
    {
        return $this->hasMany(RegistroAcesso::class);
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por_id');
    }

    public function autorizaFuncionario(Funcionario $funcionario): bool
    {
        return $this->motivosBloqueio($funcionario) === [];
    }

    public function motivosBloqueio(Funcionario $funcionario): array
    {
        $motivos = [];
        if ((int) $funcionario->empresa_id !== (int) $this->empresa_id) $motivos[] = 'Funcionário não pertence à empresa autorizada.';
        if (!in_array($this->status, ['Agendado', 'Em Andamento'], true)) $motivos[] = 'Solicitação não está ativa.';
        if (!$this->data_servico?->isSameDay(today())) $motivos[] = 'Solicitação fora da data autorizada.';

        if ($this->hora_inicio && now()->format('H:i:s') < $this->hora_inicio) $motivos[] = 'Entrada antes do horário autorizado.';
        if ($this->hora_fim && now()->format('H:i:s') > $this->hora_fim) $motivos[] = 'Entrada após o horário autorizado.';
        if (!$this->empresa->ativo) $motivos[] = 'Empresa inativa: ' . ($this->empresa->motivo_inativacao ?: 'acesso suspenso pela Segurança do Trabalho') . '.';
        if (!$this->empresa->documentacaoRegular()) $motivos[] = 'Empresa com documentação irregular.';
        if (!$funcionario->ativo) $motivos[] = 'Funcionário inativo.';
        if (!$funcionario->documentacaoRegular()) $motivos[] = 'Funcionário com documentação irregular.';
        return array_values(array_unique($motivos));
    }
}
