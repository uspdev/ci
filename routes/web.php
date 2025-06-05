<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TemplateController;

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

Route::get('grupo', [GrupoController::class, 'index'])->name('grupo.index');
Route::get('grupo/create', [GrupoController::class, 'create'])->name('grupo.create');
Route::post('grupo/create', [GrupoController::class, 'store'])->name('grupo.store');
Route::get('grupo/edit/{grupo_id}', [GrupoController::class, 'edit'])->name('grupo.edit');
Route::put('grupo/edit/{grupo_id}', [GrupoController::class, 'update'])->name('grupo.update');
Route::get('grupo/{grupo_id}', [GrupoController::class, 'show'])->name('grupo.show');
Route::delete('grupo/{grupo_id}', [GrupoController::class, 'destroy'])->name('grupo.destroy');
Route::get('grupo/select/{id}', [GrupoController::class, 'selectGrupo'])->name('grupo.select');
Route::put('/grupo/{id}/editarResponsavel', [GrupoController::class, 'editarResponsavel'])->name('grupo.editarResponsavel');
Route::put('/grupo/editarGerentes', [GrupoController::class, 'editarGerentes'])->name('grupo.editarGerentes');

Route::prefix('categorias')->name('categoria.')->group(function () {
    Route::get('/', [CategoriaController::class, 'index'])->name('index');
    Route::get('/create', [CategoriaController::class, 'create'])->name('create');
    Route::post('/', [CategoriaController::class, 'store'])->name('store');
    Route::get('/{id}', [CategoriaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [CategoriaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CategoriaController::class, 'update'])->name('update');
    Route::delete('/{id}', [CategoriaController::class, 'destroy'])->name('destroy');
});

Route::prefix('documentos')->name('documento.')->group(function () {
    Route::get('/', [DocumentoController::class, 'index'])->name('index');
    Route::get('/create', [DocumentoController::class, 'create'])->name('create');
    Route::post('/', [DocumentoController::class, 'store'])->name('store');
    Route::get('/{id}', [DocumentoController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [DocumentoController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DocumentoController::class, 'update'])->name('update');
    Route::patch('/{id}/finalizar', [DocumentoController::class, 'finalizar'])->name('finalizar');
    Route::delete('/{id}', [DocumentoController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/anexo', [DocumentoController::class, 'uploadAnexo'])->name('anexo.upload');
    Route::get('/atividades/{id}', [DocumentoController::class, 'detalharAtividade'])->name('atividade');
    Route::get('{id}/pdf', [DocumentoController::class, 'gerarPdf'])->name('pdf');
});

Route::prefix('templates')->name('template.')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/create', [TemplateController::class, 'create'])->name('create');
    Route::post('/', [TemplateController::class, 'store'])->name('store');
    Route::get('/{id}', [TemplateController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TemplateController::class, 'update'])->name('update');
    Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('destroy');
    Route::get('/{template}/pdf', [TemplateController::class, 'gerarPdf'])->name('gerarPdf');
});
