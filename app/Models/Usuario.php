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
        'permissao',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
