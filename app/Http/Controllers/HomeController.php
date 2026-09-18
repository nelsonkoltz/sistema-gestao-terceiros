<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Servico;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->user()->permissao === 'Guarita') return redirect()->route('guarita.index');
        if (auth()->user()->permissao === 'Solicitante') return redirect()->route('servicos.index');
        if (auth()->user()->permissao === 'Segurança do Trabalho') return redirect()->route('funcionarios.index');

        $counts = [
            'empresas' => Empresa::count(),
            'funcionarios' => Funcionario::where('ativo', true)->count(),
            'pendentes' => Servico::where('status', 'Agendado')->count(),
            'hoje' => Servico::whereDate('data_servico', today())
                ->whereNotIn('status', ['Finalizado', 'Cancelado'])
                ->count(),
        ];

        $proximosServicos = Servico::with(['empresa', 'setor'])
            ->whereDate('data_servico', '>=', today())
            ->whereNotIn('status', ['Finalizado', 'Cancelado'])
            ->orderBy('data_servico')
            ->limit(5)
            ->get();

        return view('home', compact('counts', 'proximosServicos'));
    }
}
