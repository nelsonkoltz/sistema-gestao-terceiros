<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Rotas do Sistema TerceirosCR
|--------------------------------------------------------------------------
| Estrutura segura, organizada e com controle de permissões
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO
|--------------------------------------------------------------------------
*/

// Login (somente visitante)
Route::get('login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

// Processa login
Route::post('login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');

// Logout
Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| HOME (DASHBOARD)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| ROTAS PROTEGIDAS (USUÁRIOS AUTENTICADOS)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\EnsureWritePermission::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | EMPRESAS
    |--------------------------------------------------------------------------
    */

    // Busca para autocomplete
    Route::get('empresas/search', [EmpresaController::class, 'search'])
        ->name('empresas.search');

    // CRUD completo
    Route::resource('empresas', EmpresaController::class);

    Route::get('empresas/{empresa}/documentos/{documento}',
        [EmpresaController::class, 'downloadDocumento'])->name('empresas.documentos.download');

    // Remover documento
    Route::delete('empresas/{empresa}/documentos/{documento}',
        [EmpresaController::class, 'deleteDocumento'])
        ->name('empresas.documentos.destroy');

    /*
    |--------------------------------------------------------------------------
    | FUNCIONÁRIOS
    |--------------------------------------------------------------------------
    */

    Route::resource('funcionarios', FuncionarioController::class);

    Route::get('funcionarios/{funcionario}/documentos/{documento}',
        [FuncionarioController::class, 'downloadDocumento'])->name('funcionarios.documentos.download');

    // Remover documento
    Route::delete('funcionarios/{funcionario}/documentos/{documento}',
        [FuncionarioController::class, 'destroyDocumento'])
        ->name('funcionarios.documentos.destroy');

    /*
    |--------------------------------------------------------------------------
    | SERVIÇOS
    |--------------------------------------------------------------------------
    */

    Route::resource('servicos', ServicoController::class);
});

/*
|--------------------------------------------------------------------------
| USUÁRIOS (ADMINISTRADOR)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::resource('usuarios', UsuarioController::class);

});
