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
        ]);

        $categoria = Categoria::create([
            'nome' => $request->nome,
            'prefixo' => $request->prefixo,
            'controlar_sequencial' => $request->controlar_sequencial ?? false,
            'grupo_id' => $grupoId,
        ]);

        session()->flash('alert-success', 'Categoria criada com sucesso!');
        return redirect()->route('categoria.index');
    }

    /**
     * Exibe uma categoria específica
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $categoria = Categoria::with(['grupo', 'templates', 'documentos'])->findOrFail($id);
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
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);

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
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        if ($categoria->grupo_id != session('grupo_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->grupo_id)) {
            abort(403, 'Você não tem permissão para editar esta categoria.');
        }

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $id . ',id,grupo_id,' . $categoria->grupo_id,
            'prefixo' => 'required|string|max:10',
        ]);

        $categoria->update([
            'nome' => $request->nome,
            'prefixo' => $request->prefixo,
            'controlar_sequencial' => $request->controlar_sequencial ?? false
        ]);

        session()->flash('alert-success', 'Categoria atualizada com sucesso!');
        return redirect()->route('categoria.index');
    }

    /**
     * Remove permanentemente uma categoria
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

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
