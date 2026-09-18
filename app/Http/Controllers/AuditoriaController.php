<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoria::with('usuario')->latest();
        if ($request->filled('usuario_id')) $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('modulo')) $query->where('modulo', $request->modulo);
        if ($request->filled('acao')) $query->where('acao', $request->acao);
        if ($request->filled('data_inicio')) $query->whereDate('created_at', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->whereDate('created_at', '<=', $request->data_fim);
        if ($request->filled('busca')) {
            $busca = trim((string) $request->busca);
            $query->where(function ($q) use ($busca) {
                $q->where('registro_id', $busca)
                    ->orWhere('ip', 'like', "%{$busca}%")
                    ->orWhereHas('usuario', fn ($u) => $u->where('name', 'like', "%{$busca}%"));
            });
        }

        $auditorias = $query->paginate(25)->withQueryString();
        $usuarios = Usuario::orderBy('name')->get(['id', 'name']);
        $modulos = Auditoria::distinct()->orderBy('modulo')->pluck('modulo');
        return view('auditorias.index', compact('auditorias', 'usuarios', 'modulos'));
    }

    public function show(Auditoria $auditoria)
    {
        $auditoria->load('usuario');
        return view('auditorias.show', compact('auditoria'));
    }
}
