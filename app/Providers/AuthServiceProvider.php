<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('grupoManager', function (User $user) {
            $hasPermission = $user
                ->permissions()
                ->where(function ($query) {
                    $query->where('name', 'like', 'manager_%')
                        ->orWhere('name', 'manager')
                        ->orWhere('name', 'admin');
                })
                ->exists();
            return $hasPermission;
        });
    }
}
