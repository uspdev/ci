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
    return redirect()->route('grupo.listar');
});

// Permite usar Gate::check('user')na view 404
Route::fallback(function(){
    return view('errors.404');
 });

 Route::prefix('grupos')->name('grupo.')->middleware('auth')->group(function () {
    Route::get('/listar', [GrupoController::class, 'listar'])->name('listar');
    Route::get('/', [GrupoController::class, 'index'])->name('index');
    Route::get('/create', [GrupoController::class, 'create'])->name('create');
    Route::post('/create', [GrupoController::class, 'store'])->name('store');
    Route::get('/edit/{grupo}', [GrupoController::class, 'edit'])->name('edit');
    Route::put('/edit/{grupo}', [GrupoController::class, 'update'])->name('update');
    Route::get('/{grupo}', [GrupoController::class, 'show'])->name('show');
    Route::delete('/{grupo}', [GrupoController::class, 'destroy'])->name('destroy');
    Route::get('/select/{grupo}', [GrupoController::class, 'selectGrupo'])->name('select');
    Route::put('/{grupo}/editarResponsavel', [GrupoController::class, 'editarResponsavel'])->name('editarResponsavel');
    Route::put('/editarGerentes', [GrupoController::class, 'editarGerentes'])->name('editarGerentes');
});

Route::prefix('categorias')->name('categoria.')->middleware('auth')->group(function () {
    Route::get('/', [CategoriaController::class, 'index'])->name('index');
    Route::get('/admin', [CategoriaController::class, 'admin'])->name('admin');
    Route::get('/create', [CategoriaController::class, 'create'])->name('create');
    Route::post('/', [CategoriaController::class, 'store'])->name('store');
    // Route::get('/{categoria}', [CategoriaController::class, 'show'])->name('show');
    Route::get('{categoria}/create', [DocumentoController::class, 'create'])->name('create.doc');
    Route::get('/{categoria}/edit', [CategoriaController::class, 'edit'])->name('edit');
    Route::get('/{categoria}/{ano?}', [DocumentoController::class, 'index'])->name('docs');
    Route::post('/{categoria}', [DocumentoController::class, 'store'])->name('store.doc');
    Route::put('/{categoria}', [CategoriaController::class, 'update'])->name('update');
    Route::delete('/{categoria}', [CategoriaController::class, 'destroy'])->name('destroy');
});

Route::prefix('documentos')->name('documento.')->middleware('auth')->group(function () {
    Route::get('/{documento}', [DocumentoController::class, 'show'])->name('show');
    Route::get('{documento}/edit', [DocumentoController::class, 'edit'])->name('edit');
    Route::put('/{documento}', [DocumentoController::class, 'update'])->name('update');
    Route::patch('/{documento}/finalizar', [DocumentoController::class, 'finalizar'])->name('finalizar');
    Route::delete('/{documento}', [DocumentoController::class, 'destroy'])->name('destroy');
    Route::get('/atividades/{activity}', [DocumentoController::class, 'detalharAtividade'])->name('atividade');
    Route::get('{documento}/pdf', [DocumentoController::class, 'gerarPdf'])->name('pdf');
    Route::post('/{documento}/copy', [DocumentoController::class, 'copy'])->name('copy');
});

Route::prefix('arquivos')->name('arquivo.')->middleware('auth')->group(function () {
    Route::post('/{documento}', [ArquivoController::class, 'upload'])->name('upload');
    Route::delete('/{arquivo}', [ArquivoController::class, 'destroy'])->name('destroy');
    Route::get('{arquivo}', [ArquivoController::class, 'download'])->name('download');
});

Route::prefix('templates')->name('template.')->middleware('auth')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/create', [TemplateController::class, 'create'])->name('create');
    Route::post('/', [TemplateController::class, 'store'])->name('store');
    Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
    Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
    Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
    Route::get('/{template}/pdf', [TemplateController::class, 'gerarPdf'])->name('gerarPdf');
});
