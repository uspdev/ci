<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;


class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = [
        'name',
        'description',
    ];

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'grupo_id');
    }

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'grupo_template', 'grupo_id', 'template_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'grupo_id');
    }

    /**
     * Define variável de sessão para o grupo ativo
     *
     * Busca o primeiro grupo cadastrado no banco de dados e armazena seu ID na sessão.
     * Caso não exista nenhum grupo, define o valor como string vazia.
     *
     * @return void
     */
    public static function setGrupoSession()
    {
        if ($grupo = Grupo::first()) {
            session([
                'grupo_id' => $grupo->id
            ]);
        } else {
            session([
                'grupo_id' => ''
            ]);
        }
    }

    /**
     * Lista os grupos que um usuário pode gerenciar
     *
     * Retorna todos os grupos para os quais o usuário possui permissões de gerenciamento
     * Se nenhum usuário for passado como parâmetro, utiliza o usuário autenticado
     *
     * @param  User|null  $user
     * @return \Illuminate\Database\Eloquent\Collection $grupos
     */
    public static function listarGruposPorUsuario(?User $user = null)
    {
        $user = $user ?? Auth::user();
        $permissions = $user->permissions->filter(function ($permission) {
            return strpos($permission->name, 'manager_') === 0;
        });

        $grupoIds = $permissions->map(function ($role) {
            return str_replace('manager_', '', $role->name);
        });

        $grupos = Grupo::whereIn('id', $grupoIds)->get();
        return $grupos;
    }

    /**
     * Retorna os usuários responsáveis por gerenciar esse grupo,  usuários que possuem a permissão referente a este grupo.
     *
     * @return \Illuminate\Database\Eloquent\Collection $users
     */
    public function users()
    {
        $users = Permission::findByName('manager_' . $this->id)->users;
        return $users;
    }
}
