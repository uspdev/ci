<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setor;
use Illuminate\Support\Facades\Auth;
use UspTheme;
use Illuminate\Support\Facades\Gate;

class SetorMenuMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (Gate::allows('setorManager', $user)) {
            $sub = [];
            $permissions = $user->permissions->filter(function ($permission) {
                return strpos($permission->name, 'manager_') === 0;
            });

            $setorIds = $permissions->map(function ($role) {
                return str_replace('manager_', '', $role->name);
            });
            if (Gate::allows('manager')) {
                $setores = Setor::all();
            } else {
                $setores = Setor::whereIn('id', $setorIds)->get();
            }

            if ($setores) {
                foreach ($setores as $setor) {
                    $sub[] = [
                        'text' => $setor->name,
                        'url' => 'setor/select/' . $setor->id,
                    ];
                }
            }

            $sub[] = [
                'text' => '<i class="fas fa-cog"></i> Configurações',
                'title' => 'Configurações',
                'url' => 'setor',
                'align' => 'right',
            ];
            $setorAtivo = Setor::find(session('setor_id'));
            \UspTheme::addMenu('setores', [
                'text' => '<span class="btn btn-sm btn-outline-danger">Setor: ' . ($setorAtivo ? $setorAtivo->name : '') . '</span>',
                'submenu' => $sub,
                'align' => 'right',
                'can' => 'user'
            ]);
        }


        return $next($request);
    }
}
