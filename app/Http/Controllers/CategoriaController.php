<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoriaController extends Controller
{
    /**
     * Exibe a lista de categorias do grupo ativo
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('grupoManager');
        \UspTheme::activeUrl('categorias');

        $grupoId = session('grupo_id');
        
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para acessar este grupo.');
        }

        $categorias = Categoria::where('grupo_id', $grupoId)->get();

        $grupo = Grupo::findOrFail($grupoId);
        return view('categoria.index', compact('categorias', 'grupo'));
    }

    /**
     * Exibe a lista de categorias do grupo ativo para gerência
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function admin()
    {
        $this->authorize('grupoManager');
        \UspTheme::activeUrl('categorias/admin');

        $grupoId = session('grupo_id');
        
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para acessar este grupo.');
        }

        $categorias = Categoria::where('grupo_id', $grupoId)->get();

        $grupo = Grupo::findOrFail($grupoId);
        return view('categoria.admin', compact('categorias', 'grupo'));
    }

    /**
     * Exibe o formulário de criação de nova categoria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('grupoManager');

        $grupoId = session('grupo_id');
        
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para criar categorias neste grupo.');
        }

        $grupo = Grupo::findOrFail($grupoId);

        return view('categoria.create', compact('grupo'));
    }

    /**
     * Armazena uma nova categoria no banco de dados
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('grupoManager');

        $grupoId = session('grupo_id');
        
        if (!$grupoId) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para criar categorias neste grupo.');
        }

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,NULL,id,grupo_id,' . $grupoId,
            'prefixo' => 'required|string|max:10',
            'email' => 'nullable|string|max:100',
            'controlar_sequencial' => 'nullable|boolean'
        ]);
        $settings['controlar_sequencial'] =  $request->controlar_sequencial ?? false;
        $categoria = Categoria::create([
            'nome' => $request->nome,
            'prefixo' => $request->prefixo,
            'email' => $request->email ?? null,
            'settings' => $settings,
            'grupo_id' => $grupoId,
        ]);

        session()->flash('alert-success', 'Categoria criada com sucesso!');
        return redirect()->route('categoria.index');
    }

    /**
     * Exibe uma categoria específica
     *
     * @param Categoria $categoria
     * @return \Illuminate\View\View
     */
    public function show(Categoria $categoria)
    {
        if ($categoria->grupo_id != session('grupo_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para visualizar esta categoria.');
        }

        return view('categoria.show', compact('categoria'));
    }

    /**
     * Exibe o formulário de edição de categoria
     * 
     * @param Categoria $categoria
     * @return \Illuminate\View\View
     */
    public function edit(Categoria $categoria)
    {
        if ($categoria->grupo_id != session('grupo_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para editar esta categoria.');
        }

        return view('categoria.edit', compact('categoria'));
    }

    /**
     * Atualiza os dados de uma categoria existente
     * 
     * @param Request $request
     * @param Categoria $categoria
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Categoria $categoria)
    {
        if ($categoria->grupo_id != session('grupo_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para editar esta categoria.');
        }

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $categoria->id . ',id,grupo_id,' . $categoria->grupo_id,
            'prefixo' => 'required|string|max:10',
            'email' => 'nullable|string|max:100',
            'controlar_sequencial' => 'nullable|boolean'
        ]);

        $settings['controlar_sequencial'] =  $request->controlar_sequencial ?? false;
        $categoria->update([
            'nome' => $request->nome,
            'prefixo' => $request->prefixo,
            'email' => $request->email ?? null,
            'settings' => $settings
        ]);

        session()->flash('alert-success', 'Categoria atualizada com sucesso!');
        return redirect()->route('categoria.admin');
    }

    /**
     * Remove permanentemente uma categoria
     * 
     * @param Categoria $categoria
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Categoria $categoria)
    {
        if ($categoria->grupo_id != session('grupo_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para excluir esta categoria.');
        }

        if ($categoria->documentos()->count() > 0) {
            session()->flash('alert-danger', 'Não é possível excluir a categoria pois existem documentos associados.');
            return redirect()->route('categoria.index');
        }

        $categoria->delete();

        session()->flash('alert-success', 'Categoria removida com sucesso!');
        return redirect()->route('categoria.index');
    }

}
