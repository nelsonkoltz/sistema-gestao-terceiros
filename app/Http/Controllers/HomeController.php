<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Servico;
use App\Models\Filial;
use App\Models\RegistroAcesso;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->user()->permissao === 'Guarita') return redirect()->route('guarita.index');
        if (auth()->user()->permissao === 'Solicitante') return redirect()->route('servicos.index');
        if (auth()->user()->permissao === 'Segurança do Trabalho') return redirect()->route('funcionarios.index');

        $consolidado = request()->boolean('consolidado');
        $consultaServicos = fn () => $consolidado ? Servico::withoutGlobalScope('filial') : Servico::query();
        $resumoFiliais = collect();
        if ($consolidado) {
            $agendados = Servico::withoutGlobalScope('filial')->where('status', 'Agendado')
                ->selectRaw('filial_id, COUNT(*) as total')->groupBy('filial_id')->pluck('total', 'filial_id');
            $presentes = RegistroAcesso::withoutGlobalScope('filial')->whereNotNull('entrada_em')->whereNull('saida_em')
                ->selectRaw('filial_id, COUNT(*) as total')->groupBy('filial_id')->pluck('total', 'filial_id');
            $resumoFiliais = Filial::orderBy('nome')->get()->map(fn ($filial) => [
                'nome' => $filial->nome, 'ativa' => $filial->ativa,
                'agendados' => $agendados[$filial->id] ?? 0, 'presentes' => $presentes[$filial->id] ?? 0,
            ]);
        }

        $counts = [
            'empresas' => Empresa::count(),
            'funcionarios' => Funcionario::where('ativo', true)->count(),
            'pendentes' => $consultaServicos()->where('status', 'Agendado')->count(),
            'hoje' => $consultaServicos()->whereDate('data_servico', today())
                ->whereNotIn('status', ['Finalizado', 'Cancelado'])
                ->count(),
        ];

        $proximosServicos = Servico::with(['empresa', 'setor'])
            ->whereDate('data_servico', '>=', today())
            ->whereNotIn('status', ['Finalizado', 'Cancelado'])
            ->orderBy('data_servico')
            ->limit(5)
            ->get();

        return view('home', compact('counts', 'proximosServicos', 'consolidado', 'resumoFiliais'));
    }
}
