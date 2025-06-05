<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Grupo;
use Illuminate\Support\Facades\Auth;
use UspTheme;
use Illuminate\Support\Facades\Gate;

class GrupoMenuMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (Gate::allows('grupoManager', $user)) {
            $sub = [];
            $permissions = $user->permissions->filter(function ($permission) {
                return strpos($permission->name, 'manager_') === 0;
            });

            $grupoIds = $permissions->map(function ($role) {
                return str_replace('manager_', '', $role->name);
            });
            if (Gate::allows('manager')) {
                $grupos = Grupo::all();
            } else {
                $grupos = Grupo::whereIn('id', $grupoIds)->get();
            }

            if ($grupos) {
                foreach ($grupos as $grupo) {
                    $sub[] = [
                        'text' => $grupo->name,
                        'url' => 'grupo/select/' . $grupo->id,
                    ];
                }
            }

            $sub[] = [
                'text' => '<i class="fas fa-cog"></i> Configurações',
                'title' => 'Configurações',
                'url' => 'grupo',
                'align' => 'right',
            ];
            $grupoAtivo = Grupo::find(session('grupo_id'));
            \UspTheme::addMenu('grupos', [
                'text' => '<span class="btn btn-sm btn-outline-danger">Grupo: ' . ($grupoAtivo ? $grupoAtivo->name : '') . '</span>',
                'submenu' => $sub,
                'align' => 'right',
                'can' => 'user'
            ]);
        }


        return $next($request);
    }
}
