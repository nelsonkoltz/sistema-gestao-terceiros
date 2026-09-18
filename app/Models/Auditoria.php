<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';
    protected $guarded = [];
    protected $casts = ['dados_anteriores' => 'array', 'dados_novos' => 'array'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function getNavegadorResumidoAttribute(): string
    {
        $agent = (string) $this->user_agent;
        if ($agent === '') return 'Não disponível';
        $navegador = 'Navegador desconhecido';
        foreach ([
            '/Edg\/([\d.]+)/' => 'Microsoft Edge',
            '/OPR\/([\d.]+)/' => 'Opera',
            '/Chrome\/([\d.]+)/' => 'Google Chrome',
            '/Firefox\/([\d.]+)/' => 'Mozilla Firefox',
            '/Version\/([\d.]+).*Safari\//' => 'Safari',
        ] as $padrao => $nome) {
            if (preg_match($padrao, $agent, $resultado)) {
                $navegador = $nome.' '.explode('.', $resultado[1])[0];
                break;
            }
        }
        $sistema = 'Sistema desconhecido';
        if (str_contains($agent, 'Windows NT 10.0')) $sistema = 'Windows';
        elseif (str_contains($agent, 'Android')) $sistema = 'Android';
        elseif (preg_match('/iPhone|iPad/', $agent)) $sistema = 'iOS';
        elseif (str_contains($agent, 'Mac OS X')) $sistema = 'macOS';
        elseif (str_contains($agent, 'Linux')) $sistema = 'Linux';
        return $navegador.' · '.$sistema;
    }

    public function getConexaoLocalAttribute(): bool
    {
        return $this->ip === '127.0.0.1' || $this->ip === '::1'
            || (bool) preg_match('/^172\.(1[6-9]|2\d|3[01])\./', (string) $this->ip);
    }
}
