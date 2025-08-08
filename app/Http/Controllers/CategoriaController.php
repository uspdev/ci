<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoriaController extends Controller
{
    private function verifyGrupo()
    {
        $grupoId = session('grupo_id');

        if (!Gate::allows('manager') && !Auth::user()->hasPermissionTo('manager_' . $grupoId)) {
            abort(403, 'Você não tem permissão para acessar este grupo.');
        }
    }
    /**
     * Exibe a lista de categorias do grupo ativo
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('grupoManager');
        \UspTheme::activeUrl('categorias');

        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();
        $grupoId = session('grupo_id');

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

        $this->verifyGrupo();

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
        
        $this->verifyGrupo();

        $grupo = Grupo::findOrFail($grupoId);
        $templates = $grupo->templates()->get();

        return view('categoria.create', compact('grupo', 'templates'));
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
        
        $this->verifyGrupo();
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,NULL,id,grupo_id,' . $grupoId,
            'prefixo' => 'required|string|max:255',
            'email' => 'nullable|string|max:100',
            'controlar_sequencial' => 'nullable|boolean',
            'templates' => 'nullable|array',
            'templates.*' => 'exists:templates,id',
        ]);
        $settings['controlar_sequencial'] =  $request->controlar_sequencial ?? false;
        $categoria = Categoria::create([
            'nome' => $request->nome,
            'prefixo' => $request->prefixo,
            'email' => $request->email ?? null,
            'settings' => $settings,
            'grupo_id' => $grupoId,
        ]);
        $templatesIds = $request->input('templates', []);

        $templatesValidos = Template::whereIn('id', $templatesIds)
            ->whereHas('grupos', function ($query) use ($categoria) {
                $query->where('grupo_id', $categoria->grupo_id);
            })
            ->pluck('id')
            ->toArray();

        if ($request->has('templates')) {
            $categoria->templates()->sync($templatesValidos);
        }

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
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();

        if ($categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Categoria não pertence ao grupo selecionado.');
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
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }

        $this->verifyGrupo();

        if ($categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Categoria não pertence ao grupo selecionado.');
        }

        $templates = $categoria->grupo->templates()->get();
        return view('categoria.edit', compact('categoria', 'templates'));
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
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }
        
        $this->verifyGrupo();

        if ($categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Categoria não pertence ao grupo selecionado.');
        }

        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $categoria->id . ',id,grupo_id,' . $categoria->grupo_id,
            'prefixo' => 'required|string|max:255',
            'email' => 'nullable|string|max:100',
            'controlar_sequencial' => 'nullable|boolean',
            'templates' => 'nullable|array',
            'templates.*' => 'exists:templates,id',
        ]);
        $templatesIds = $request->input('templates', []);

        $templatesValidos = Template::whereIn('id', $templatesIds)
            ->whereHas('grupos', function ($query) use ($categoria) {
                $query->where('grupo_id', $categoria->grupo_id);
            })
            ->pluck('id')
            ->toArray();

        if ($request->has('templates')) {
            $categoria->templates()->sync($templatesValidos);
        } else {
            $categoria->templates()->detach();
        }

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
        if (!session('grupo_id')) {
            return redirect()->route('grupo.index')->with('alert-warning', 'Selecione um grupo primeiro.');
        }
        
        $this->verifyGrupo();

        if ($categoria->grupo_id != session('grupo_id')) {
            abort(403, 'Categoria não pertence ao grupo selecionado.');
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
