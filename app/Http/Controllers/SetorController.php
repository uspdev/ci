<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setor;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class SetorController extends Controller
{
    /**
     * Exibe a lista de setores conforme as permissões do usuário
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('setorManager');

        if (Gate::allows('manager')) {
            $setores = Setor::all();
        } else {
            $setores = Setor::listarSetoresPorUsuario();
        }

        $gerentes = User::whereHas('permissions', function ($query) {
            $query->where('name', 'manager');
        })->get();

        return view('setor.index', compact('setores', 'gerentes'));
    }

    /**
     * Exibe o formulário de criação de novo setor
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('manager');

        return view('setor.create');
    }

    /**
     * Armazena um novo setor no banco de dados, cria permissão específica e associa ao criador
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
        
        $setor = Setor::create([
            'name' => $request->name, 
            'description' => $request->description
        ]);

        $permission = Permission::firstOrCreate(['name' => 'manager_' . $setor->id]);

        $this->criarCategoriasPadrao($setor);

        Setor::setSetorSession();

        session()->flash('alert-success', 'Setor criado com sucesso! Categorias padrão (Memorando e Ofício) foram criadas automaticamente.');
        return redirect()->route('setor.edit', $setor);
    }

    private function criarCategoriasPadrao(Setor $setor)
    {
        $categoriasPadrao = [
            'Memorando',
            'Ofício'
        ];

        foreach ($categoriasPadrao as $nomeCategoria) {
            Categoria::create([
                'nome' => $nomeCategoria,
                'setor_id' => $setor->id
            ]);
        }
    }

    /**
     * Verifica permissão específica do setor ou acesso de admin e exibe o formulário de edição de setor
     * 
     * @param int $setor_id ID do setor
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($setor_id)
    {
        if (! Auth::check() || (! Auth::user()->hasPermissionTo('manager_' . $setor_id) && ! Gate::allows('manager'))) {
            return redirect()->route('setor.show', ['setor_id' => $setor_id]);
        }

        $setor = Setor::findOrFail($setor_id);
        return view('setor.edit', compact('setor'));
    }

    /**
     * Atualiza os dados de um setor existente
     * 
     * @param Request $request
     * @param int $setor_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $setor_id)
    {
        if (! Auth::check() || (! Auth::user()->hasPermissionTo('manager_' . $setor_id) && ! Gate::allows('manager'))) {
            return redirect()->route('setor.show', ['setor_id' => $setor_id]);
        }
        $setor = Setor::findOrFail($setor_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $setor->update([
            'name' => $request->name,
            'description' => $request->description
        ]);
        Setor::setSetorSession();
        session()->flash('alert-success', 'Setor atualizado com sucesso!');
        return redirect()->route('setor.edit', ['setor_id' => $setor_id]);
    }

    /**
     * Remove permanentemente um setor, a permissão associada e revoga acesso de todos usuários
     * 
     * @param int $setor_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($setor_id)
    {
        if (! Auth::check() || (! Auth::user()->hasPermissionTo('manager_' . $setor_id) && ! Gate::allows('manager'))) {
            return redirect()->route('setor.show', ['setor_id' => $setor_id]);
        }
        $setor = Setor::findOrFail($setor_id);

        $permission = Permission::findByName('manager_' . $setor->id);
        $permission->users()->detach();
        $permission->delete();

        $setor->delete();
        Setor::setSetorSession();

        session()->flash('alert-success', 'Setor removido com sucesso!');
        return redirect()->route('setor.index');
    }

    /**
     * Define o setor ativo na sessão
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selectSetor($id)
    {
        $setor = Setor::find($id);

        session([
            'setor_id' => $setor->id
        ]);

        return redirect(route('documento.index'));
    }

    /**
     * Gerencia adição/remoção de responsáveis pelo setor
     * 
     * Manipula permissões específicas do setor (manager_{id})

     * @param int $setor_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function editarResponsavel($setor_id, Request $request)
    {
        $codpes_rem = $request->input('codpes_rem');
        $codpes_add = $request->input('codpes_add');
        $setor_id = $request->input('setor_id');

        $adminPermission = 'manager_' . $setor_id;
        $user = Auth::user();

        if (! $user->hasPermissionTo($adminPermission) && ! Gate::allows('manager')) {
            return response()->json(['alert-danger' => 'Você não tem permissão para gerenciar este setor.'], 403);
        }

        if ($codpes_rem) {
            $userToRemove = User::where('codpes', $codpes_rem)->first();
            if ($userToRemove) {
                $userToRemove->revokePermissionTo($adminPermission);
                $request->session()->flash('alert-success', 'Usuário removido com sucesso!');
            }
        }

        if ($codpes_add) {
            $userToAdd = User::findOrCreateFromReplicado($codpes_add);
            if ($userToAdd) {
                $userToAdd->givePermissionTo($adminPermission);
                $request->session()->flash('alert-success', 'Usuário adicionado com sucesso!');
            }
        }

        return redirect()->route('setor.edit', ['setor_id' => $setor_id]);
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

        return redirect()->route('setor.index');
    }
}
