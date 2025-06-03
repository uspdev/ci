<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Setor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoriaController extends Controller
{
    /**
     * Exibe a lista de categorias do setor ativo
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('setorManager');
        \UspTheme::activeUrl('categorias');

        $setorId = session('setor_id');
        
        if (!$setorId) {
            return redirect()->route('setor.index')->with('alert-warning', 'Selecione um setor primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $setorId)) {
            abort(403, 'Você não tem permissão para acessar este setor.');
        }

        $categorias = Categoria::where('setor_id', $setorId)->get();

        $setor = Setor::findOrFail($setorId);
        return view('categoria.index', compact('categorias', 'setor'));
    }


    /**
     * Exibe o formulário de criação de nova categoria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('setorManager');

        $setorId = session('setor_id');
        
        if (!$setorId) {
            return redirect()->route('setor.index')->with('alert-warning', 'Selecione um setor primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $setorId)) {
            abort(403, 'Você não tem permissão para criar categorias neste setor.');
        }

        $setor = Setor::findOrFail($setorId);

        return view('categoria.create', compact('setor'));
    }

    /**
     * Armazena uma nova categoria no banco de dados
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('setorManager');

        $setorId = session('setor_id');
        
        if (!$setorId) {
            return redirect()->route('setor.index')->with('alert-warning', 'Selecione um setor primeiro.');
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $setorId)) {
            abort(403, 'Você não tem permissão para criar categorias neste setor.');
        }

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,NULL,id,setor_id,' . $setorId,
            'abreviacao' => 'required|string|max:10',
        ]);

        $categoria = Categoria::create([
            'nome' => $request->nome,
            'abreviacao' => $request->abreviacao,
            'setor_id' => $setorId,
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
        $categoria = Categoria::with(['setor', 'templates', 'documentos'])->findOrFail($id);
        if ($categoria->setor_id != session('setor_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->setor_id)) {
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

        if ($categoria->setor_id != session('setor_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->setor_id)) {
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

        if ($categoria->setor_id != session('setor_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->setor_id)) {
            abort(403, 'Você não tem permissão para editar esta categoria.');
        }

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $id . ',id,setor_id,' . $categoria->setor_id,
            'abreviacao' => 'required|string|max:10',
        ]);

        $categoria->update([
            'nome' => $request->nome,
            'abreviacao' => $request->abreviacao,
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

        if ($categoria->setor_id != session('setor_id')) {
            abort(404);
        }

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $categoria->setor_id)) {
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
