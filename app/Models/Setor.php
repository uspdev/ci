<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;


class Setor extends Model
{
    use HasFactory;

    protected $table = 'setors';

    protected $fillable = [
        'name',
        'description',
    ];

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'setor_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'setor_id');
    }

    /**
     * Define variável de sessão para o setor ativo
     *
     * Busca o primeiro setor cadastrado no banco de dados e armazena seu ID na sessão.
     * Caso não exista nenhum setor, define o valor como string vazia.
     *
     * @return void
     */
    public static function setSetorSession()
    {
        if ($setor = Setor::first()) {
            session([
                'setor_id' => $setor->id
            ]);
        } else {
            session([
                'setor_id' => ''
            ]);
        }
    }

    /**
     * Lista os setores que um usuário pode gerenciar
     *
     * Retorna todos os setores para os quais o usuário possui permissões de gerenciamento
     * Se nenhum usuário for passado como parâmetro, utiliza o usuário autenticado
     *
     * @param  User|null  $user
     * @return \Illuminate\Database\Eloquent\Collection $setores
     */
    public static function listarSetoresPorUsuario(?User $user = null)
    {
        $user = $user ?? Auth::user();
        $permissions = $user->permissions->filter(function ($permission) {
            return strpos($permission->name, 'manager_') === 0;
        });

        $setorIds = $permissions->map(function ($role) {
            return str_replace('manager_', '', $role->name);
        });

        $setores = Setor::whereIn('id', $setorIds)->get();
        return $setores;
    }

    /**
     * Retorna os usuários responsáveis por gerenciar esse setor,  usuários que possuem a permissão referente a este setor.
     *
     * @return \Illuminate\Database\Eloquent\Collection $users
     */
    public function users()
    {
        $users = Permission::findByName('manager_' . $this->id)->users;
        return $users;
    }
}
