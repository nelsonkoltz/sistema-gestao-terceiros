<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Concerns\Auditable;

class Usuario extends Authenticatable
{
    use HasFactory, Auditable;

    protected $table = 'usuarios';

    protected $fillable = [
        'name',
        'setor',
        'username',
        'email',
        'password',
        'trocar_senha',
        'senha_alterada_em',
        'permissao',
        'ativo',
        'motivo_inativacao',
        'inativado_por_id',
        'inativado_em',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = ['ativo' => 'boolean', 'trocar_senha' => 'boolean', 'inativado_em' => 'datetime', 'senha_alterada_em' => 'datetime'];

    public function inativadoPor()
    {
        return $this->belongsTo(self::class, 'inativado_por_id');
    }

    public function filiais()
    {
        return $this->belongsToMany(Filial::class, 'filial_usuario')
            ->withPivot('principal')->withTimestamps();
    }

    public function filialPrincipal()
    {
        return $this->filiais()->wherePivot('principal', true);
    }
}
