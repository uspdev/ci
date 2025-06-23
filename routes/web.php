<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\DocumentoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Permite usar Gate::check('user')na view 404
Route::fallback(function(){
    return view('errors.404');
 });

 Route::prefix('grupos')->name('grupo.')->middleware('auth')->group(function () {
    Route::get('/', [GrupoController::class, 'index'])->name('index');
    Route::get('/create', [GrupoController::class, 'create'])->name('create');
    Route::post('/create', [GrupoController::class, 'store'])->name('store');
    Route::get('/edit/{grupo_id}', [GrupoController::class, 'edit'])->name('edit');
    Route::put('/edit/{grupo_id}', [GrupoController::class, 'update'])->name('update');
    Route::get('/{grupo_id}', [GrupoController::class, 'show'])->name('show');
    Route::delete('/{grupo_id}', [GrupoController::class, 'destroy'])->name('destroy');
    Route::get('/select/{id}', [GrupoController::class, 'selectGrupo'])->name('select');
    Route::put('/{id}/editarResponsavel', [GrupoController::class, 'editarResponsavel'])->name('editarResponsavel');
    Route::put('/editarGerentes', [GrupoController::class, 'editarGerentes'])->name('editarGerentes');
});

Route::prefix('categorias')->name('categoria.')->middleware('auth')->group(function () {
    Route::get('/', [CategoriaController::class, 'index'])->name('index');
    Route::get('/admin', [CategoriaController::class, 'admin'])->name('admin');
    Route::get('/create', [CategoriaController::class, 'create'])->name('create');
    Route::post('/', [CategoriaController::class, 'store'])->name('store');
    Route::get('/{id}', [CategoriaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [CategoriaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CategoriaController::class, 'update'])->name('update');
    Route::delete('/{id}', [CategoriaController::class, 'destroy'])->name('destroy');
});

Route::prefix('documentos')->name('documento.')->middleware('auth')->group(function () {
    Route::get('categoria/{categoria}/{ano?}', [DocumentoController::class, 'index'])->name('index');
    Route::get('{categoria}/create', [DocumentoController::class, 'create'])->name('create');
    Route::post('categoria/{categoria}', [DocumentoController::class, 'store'])->name('store');
    Route::get('/{id}', [DocumentoController::class, 'show'])->name('show');
    Route::get('categoria/{categoria}/{id}/edit', [DocumentoController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DocumentoController::class, 'update'])->name('update');
    Route::patch('/{id}/finalizar', [DocumentoController::class, 'finalizar'])->name('finalizar');
    Route::delete('/{id}', [DocumentoController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/arquivo', [DocumentoController::class, 'uploadArquivo'])->name('arquivo.upload');
    Route::get('/atividades/{id}', [DocumentoController::class, 'detalharAtividade'])->name('atividade');
    Route::get('{id}/pdf', [DocumentoController::class, 'gerarPdf'])->name('pdf');
    Route::post('/{id}/copy', [DocumentoController::class, 'copy'])->name('copy');
});

Route::prefix('arquivos')->name('arquivo.')->middleware('auth')->group(function () {
    Route::post('/{id}', [ArquivoController::class, 'upload'])->name('upload');
    Route::delete('/{id}', [ArquivoController::class, 'destroy'])->name('destroy');
});

Route::prefix('templates')->name('template.')->middleware('auth')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/create', [TemplateController::class, 'create'])->name('create');
    Route::post('/', [TemplateController::class, 'store'])->name('store');
    Route::get('/{id}', [TemplateController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TemplateController::class, 'update'])->name('update');
    Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('destroy');
    Route::get('/{template}/pdf', [TemplateController::class, 'gerarPdf'])->name('gerarPdf');
});
