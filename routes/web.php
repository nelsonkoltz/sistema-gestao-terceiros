<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\GestaoDocumentoController;
use App\Http\Controllers\GuaritaController;
use App\Http\Controllers\AlertaDocumentoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\RecuperacaoSenhaController;

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

Route::middleware('guest')->group(function () {
    Route::get('esqueci-minha-senha', [RecuperacaoSenhaController::class, 'solicitar'])->name('senha.solicitar');
    Route::post('esqueci-minha-senha', [RecuperacaoSenhaController::class, 'enviar'])->middleware('throttle:3,10')->name('senha.enviar');
    Route::get('redefinir-senha/{token}', [RecuperacaoSenhaController::class, 'redefinir'])->name('senha.redefinir');
    Route::post('redefinir-senha', [RecuperacaoSenhaController::class, 'atualizar'])->middleware('throttle:5,10')->name('senha.atualizar');
});

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

Route::middleware('auth')->group(function () {
    Route::get('minha-conta', [UsuarioController::class, 'minhaConta'])->name('minha-conta.index');
    Route::put('minha-conta/senha', [UsuarioController::class, 'alterarMinhaSenha'])->name('minha-conta.senha');
});

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
        ->middleware('role:Administrador,Segurança do Trabalho')
        ->name('empresas.search');

    // CRUD completo
    Route::resource('empresas', EmpresaController::class)->middleware('role:Administrador,Segurança do Trabalho');

    Route::get('empresas/{empresa}/documentos/{documento}',
        [EmpresaController::class, 'downloadDocumento'])->middleware('role:Administrador,Segurança do Trabalho')->name('empresas.documentos.download');

    // Remover documento
    Route::delete('empresas/{empresa}/documentos/{documento}',
        [EmpresaController::class, 'deleteDocumento'])
        ->middleware('role:Administrador,Segurança do Trabalho')
        ->name('empresas.documentos.destroy');

    /*
    |--------------------------------------------------------------------------
    | FUNCIONÁRIOS
    |--------------------------------------------------------------------------
    */

    Route::resource('funcionarios', FuncionarioController::class)->middleware('role:Administrador,Segurança do Trabalho');

    Route::get('funcionarios/{funcionario}/documentos/{documento}',
        [FuncionarioController::class, 'downloadDocumento'])->middleware('role:Administrador,Segurança do Trabalho')->name('funcionarios.documentos.download');

    // Remover documento
    Route::delete('funcionarios/{funcionario}/documentos/{documento}',
        [FuncionarioController::class, 'destroyDocumento'])
        ->middleware('role:Administrador,Segurança do Trabalho')
        ->name('funcionarios.documentos.destroy');

    /*
    |--------------------------------------------------------------------------
    | SERVIÇOS
    |--------------------------------------------------------------------------
    */

    // Segurança do Trabalho consulta o contexto dos terceiros, sem alterar a solicitação.
    Route::resource('servicos', ServicoController::class)
        ->except(['index', 'show'])
        ->middleware('role:Administrador,Solicitante');
    Route::post('servicos/{servico}/cancelar', [ServicoController::class, 'cancelar'])
        ->middleware('role:Administrador,Solicitante')
        ->name('servicos.cancelar');
    Route::resource('servicos', ServicoController::class)
        ->only(['index', 'show'])
        ->middleware('role:Administrador,Solicitante,Segurança do Trabalho');
});

/*
|--------------------------------------------------------------------------
| USUÁRIOS (ADMINISTRADOR)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::resource('usuarios', UsuarioController::class);
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');
    Route::get('auditorias/{auditoria}', [AuditoriaController::class, 'show'])->name('auditorias.show');

    Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
    Route::put('configuracoes/job-documentos', [ConfiguracaoController::class, 'updateJob'])->name('configuracoes.job');
    Route::post('configuracoes/job-documentos/executar', [ConfiguracaoController::class, 'executarJob'])->name('configuracoes.job.executar');
    Route::put('configuracoes/email', [ConfiguracaoController::class, 'updateEmail'])->name('configuracoes.email');
    Route::post('configuracoes/email/testar', [ConfiguracaoController::class, 'testarEmail'])->name('configuracoes.email.testar');
    Route::put('configuracoes/alertas-documentais', [ConfiguracaoController::class, 'updateAlertas'])->name('configuracoes.alertas');
    Route::put('configuracoes/validade-documentos', [ConfiguracaoController::class, 'updateValidade'])->name('configuracoes.validade');
    Route::put('configuracoes/sessao', [ConfiguracaoController::class, 'updateSessao'])->name('configuracoes.sessao');

});

Route::middleware(['auth', 'seguranca_trabalho'])->group(function () {
    Route::get('alertas-documentais', [AlertaDocumentoController::class, 'index'])->name('alertas-documentos.index');
    Route::post('alertas-documentais/enviar', [AlertaDocumentoController::class, 'enviarEmails'])->name('alertas-documentos.enviar');
    Route::post('empresas/{empresa}/documentos', [GestaoDocumentoController::class, 'storeEmpresa'])->name('empresas.documentos.store');
    Route::post('funcionarios/{funcionario}/documentos', [GestaoDocumentoController::class, 'storeFuncionario'])->name('funcionarios.documentos.store');
});

Route::middleware(['auth', 'role:Administrador,Guarita'])->group(function () {
    Route::get('guarita', [GuaritaController::class, 'index'])->name('guarita.index');
    Route::get('guarita/historico', [GuaritaController::class, 'historico'])->name('guarita.historico');
    Route::get('guarita/historico/exportar', [GuaritaController::class, 'exportarHistorico'])->name('guarita.historico.exportar');
    Route::get('guarita/ocorrencias', [GuaritaController::class, 'ocorrencias'])->name('guarita.ocorrencias');
    Route::get('guarita/ocorrencias/exportar', [GuaritaController::class, 'exportarOcorrencias'])->name('guarita.ocorrencias.exportar');
    Route::post('guarita/funcionarios/{funcionario}/ocorrencia', [GuaritaController::class, 'registrarOcorrencia'])->name('guarita.ocorrencias.registrar');
    Route::post('guarita/funcionarios/{funcionario}/entrada', [GuaritaController::class, 'entrada'])->name('guarita.entrada');
    Route::put('guarita/registros/{registro}/saida', [GuaritaController::class, 'saida'])->name('guarita.saida');
});
