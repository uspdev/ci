<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\CategoriaController;
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

Route::get('setor', [SetorController::class, 'index'])->name('setor.index');
Route::get('setor/create', [SetorController::class, 'create'])->name('setor.create');
Route::post('setor/create', [SetorController::class, 'store'])->name('setor.store');
Route::get('setor/edit/{setor_id}', [SetorController::class, 'edit'])->name('setor.edit');
Route::put('setor/edit/{setor_id}', [SetorController::class, 'update'])->name('setor.update');
Route::get('setor/{setor_id}', [SetorController::class, 'show'])->name('setor.show');
Route::delete('setor/{setor_id}', [SetorController::class, 'destroy'])->name('setor.destroy');
Route::get('setor/select/{id}', [SetorController::class, 'selectSetor'])->name('setor.select');
Route::put('/setor/{id}/editarResponsavel', [SetorController::class, 'editarResponsavel'])->name('setor.editarResponsavel');
Route::put('/setor/editarGerentes', [SetorController::class, 'editarGerentes'])->name('setor.editarGerentes');

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
