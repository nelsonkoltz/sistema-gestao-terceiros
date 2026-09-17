<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';
    protected $fillable = ['chave', 'valor'];

    public static function valor(string $chave, $padrao = null)
    {
        return static::where('chave', $chave)->value('valor') ?? $padrao;
    }

    public static function booleano(string $chave, bool $padrao = false): bool
    {
        return filter_var(static::valor($chave, $padrao ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }
}
