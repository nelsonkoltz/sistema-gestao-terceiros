<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ServicoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas do Sistema TerceirosCR
|--------------------------------------------------------------------------
| Estrutura ajustada para funcionamento correto do sistema,
| incluindo autenticação, uploads e controle de permissões.
|--------------------------------------------------------------------------
*/


// ========================================
// LOGIN / LOGOUT
// ========================================

// Exibe tela de login (somente para visitantes)
Route::get('login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

// Processa login
Route::post('login', [LoginController::class, 'login'])
    ->name('login.post');

// Logout
Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


// ========================================
// HOME (protegida por autenticação)
// ========================================
Route::middleware('auth')->get('/', [HomeController::class, 'index'])->name('home');


// ========================================
// EMPRESAS
// ========================================

// Rota de busca — ⚠️ pública (para autocomplete, se necessário)
Route::get('/empresas/search', [EmpresaController::class, 'search'])
    ->name('empresas.search');

// Demais rotas protegidas
Route::middleware('auth')->group(function () {

    // CRUD de empresas
    Route::get('empresas', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::get('empresas/create', [EmpresaController::class, 'create'])->name('empresas.create');
    Route::post('empresas', [EmpresaController::class, 'store'])->name('empresas.store');
    Route::get('empresas/{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');
    Route::get('empresas/{empresa}/edit', [EmpresaController::class, 'edit'])->name('empresas.edit');
    Route::put('empresas/{empresa}', [EmpresaController::class, 'update'])->name('empresas.update');
    Route::delete('empresas/{empresa}', [EmpresaController::class, 'destroy'])->name('empresas.destroy');

    // Excluir documento de empresa
    Route::delete('empresas/{empresa}/documentos/{documento}', [EmpresaController::class, 'deleteDocumento'])
        ->name('empresas.deleteDocumento');
});


// ========================================
// FUNCIONÁRIOS
// ========================================
Route::middleware('auth')->group(function () {

    // CRUD de funcionários
    Route::resource('funcionarios', FuncionarioController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Excluir documento de funcionário
    Route::delete('funcionarios/{funcionario}/documentos/{documento}', [FuncionarioController::class, 'destroyDocumento'])
        ->name('funcionarios.documentos.destroy');
});


// ========================================
// SERVIÇOS
// ========================================
Route::middleware('auth')->group(function () {

    // CRUD completo de serviços
    Route::get('servicos', [ServicoController::class, 'index'])->name('servicos.index');
    Route::get('servicos/create', [ServicoController::class, 'create'])->name('servicos.create');
    Route::post('servicos', [ServicoController::class, 'store'])->name('servicos.store');
    Route::get('servicos/{servico}', [ServicoController::class, 'show'])->name('servicos.show');
    Route::get('servicos/{servico}/edit', [ServicoController::class, 'edit'])->name('servicos.edit');
    Route::put('servicos/{servico}', [ServicoController::class, 'update'])->name('servicos.update');
    Route::delete('servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');
});


// ========================================
// USUÁRIOS (ADMINISTRADOR)
// ========================================
Route::middleware('auth')->group(function () {

    // CRUD completo de usuários
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('usuarios/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::get('usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
});
