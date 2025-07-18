<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Grupo;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class GrupoController extends Controller
{
    /**
     * Exibe a lista de grupos conforme as permissões do usuário
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('grupoManager');

        if (Gate::allows('manager')) {
            $grupos = Grupo::all();
        } else {
            $grupos = Grupo::listarGruposPorUsuario();
        }

        $gerentes = User::whereHas('permissions', function ($query) {
            $query->where('name', 'manager');
        })->get();

        return view('grupo.index', compact('grupos', 'gerentes'));
    }

    /**
     * Exibe o formulário de criação de novo grupo
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('manager');

        return view('grupo.create');
    }

    /**
     * Armazena um novo grupo no banco de dados, cria permissão específica e associa ao criador
     * 
     * @param Request $request Dados do formulário
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('manager');

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $grupo = Grupo::create([
            'name' => $request->name, 
            'description' => $request->description
        ]);

        $permission = Permission::firstOrCreate(['name' => 'manager_' . $grupo->id]);

        $this->criarCategoriasPadrao($grupo);

        Grupo::setGrupoSession();

        session()->flash('alert-success', 'Grupo criado com sucesso! Categorias padrão (Memorando e Ofício) foram criadas automaticamente.');
        return redirect()->route('grupo.edit', $grupo);
    }

    private function criarCategoriasPadrao(Grupo $grupo)
    {
        $categoriasPadrao = ['Memorando', 'Ofício'];
        $settings['controlar_sequencial'] = true;
        foreach ($categoriasPadrao as $nomeCategoria) {
            Categoria::create([
                'nome' => $nomeCategoria,
                'settings' => $settings,
                'grupo_id' => $grupo->id
            ]);
        }
    }

    /**
     * Verifica permissão específica do grupo ou acesso de admin e exibe o formulário de edição de grupo
     * 
     * @param Grupo $grupo
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Grupo $grupo)
    {
        if (! Auth::check() || (! Auth::user()->hasPermissionTo('manager_' . $grupo->id) && ! Gate::allows('manager'))) {
            return redirect()->route('grupo.show', $grupo);
        }

        return view('grupo.edit', compact('grupo'));
    }

    /**
     * Atualiza os dados de um grupo existente
     * 
     * @param Request $request
     * @param Grupo $grupo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Grupo $grupo)
    {
        if (! Auth::check() || (! Auth::user()->hasPermissionTo('manager_' . $grupo->id) && ! Gate::allows('manager'))) {
            return redirect()->route('grupo.show', $grupo);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $grupo->update([
            'name' => $request->name,
            'description' => $request->description
        ]);
        Grupo::setGrupoSession();
        session()->flash('alert-success', 'Grupo atualizado com sucesso!');
        return redirect()->route('grupo.edit', $grupo);
    }

    /**
     * Remove permanentemente um grupo, a permissão associada e revoga acesso de todos usuários
     * 
     * @param Grupo $grupo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Grupo $grupo)
    {
        if (! Auth::check() || (! Auth::user()->hasPermissionTo('manager_' . $grupo->id) && ! Gate::allows('manager'))) {
            return redirect()->route('grupo.show', $grupo);
        }

        $permission = Permission::findByName('manager_' . $grupo->id);
        $permission->users()->detach();
        $permission->delete();

        $grupo->delete();
        Grupo::setGrupoSession();

        session()->flash('alert-success', 'Grupo removido com sucesso!');
        return redirect()->route('grupo.index');
    }

    /**
     * Define o grupo ativo na sessão
     *
     * @param Grupo $grupo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selectGrupo(Grupo $grupo)
    {
        session([
            'grupo_id' => $grupo->id
        ]);

        return redirect(route('categoria.index'));
    }

    /**
     * Gerencia adição/remoção de responsáveis pelo grupo
     * 
     * Manipula permissões específicas do grupo (manager_{id})

     * @param Grupo $grupo
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function editarResponsavel(Grupo $grupo, Request $request)
    {
        $codpes_rem = $request->input('codpes_rem');
        $codpes_add = $request->input('codpes_add');

        $groupPermission = 'manager_' . $grupo->id;
        $user = Auth::user();

        if (! $user->hasPermissionTo($groupPermission) && ! Gate::allows('manager')) {
            return response()->json(['alert-danger' => 'Você não tem permissão para gerenciar este grupo.'], 403);
        }

        if ($codpes_rem) {
            $userToRemove = User::where('codpes', $codpes_rem)->first();
            if ($userToRemove) {
                $userToRemove->revokePermissionTo($groupPermission);
                $request->session()->flash('alert-success', 'Usuário removido com sucesso!');
            }
        }

        if ($codpes_add) {
            $userToAdd = User::findOrCreateFromReplicado($codpes_add);
            if ($userToAdd) {
                $userToAdd->givePermissionTo($groupPermission);
                $request->session()->flash('alert-success', 'Usuário adicionado com sucesso!');
            }
        }

        return redirect()->route('grupo.edit', $grupo);
    }

    /**
     * Gerencia adição/remoção de gerentes do sistema
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function editarGerentes(Request $request)
    {
        $this->authorize('manager');
        $codpes_rem = $request->input('codpes_rem');
        $codpes_add = $request->input('codpes_add');

        $manager = 'manager';
        $guardName = 'senhaunica';

        if ($codpes_rem) {
            $userToRemove = User::where('codpes', $codpes_rem)->first();
            if ($userToRemove) {
                $userToRemove->guard_name = $guardName;
                $userToRemove->revokePermissionTo($manager);
                $request->session()->flash('alert-success', 'Gerente removido com sucesso!');
            }
        }

        if ($codpes_add) {
            $userToAdd = User::findOrCreateFromReplicado($codpes_add);
            if ($userToAdd) {
                $userToAdd->guard_name = $guardName;
                $userToAdd->givePermissionTo($manager);
                $request->session()->flash('alert-success', 'Gerente adicionado com sucesso!');
            }
        }

        return redirect()->route('grupo.index');
    }
}
