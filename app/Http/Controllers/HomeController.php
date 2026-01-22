<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // Se já tiver Models, alimente as contagens:
        // $counts = [
        //     'empresas' => \App\Models\Empresa::count(),
        //     'servicos' => \App\Models\Servico::count(),
        //     'funcionarios' => \App\Models\Funcionario::count(),
        //     'usuarios' => \App\Models\User::count(),
        // ];
        $counts = [];

        return view('home', compact('counts'));
    }
}
