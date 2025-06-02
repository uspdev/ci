<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // https://github.com/spatie/laravel-activitylog/issues/39
        Activity::saving(function (Activity $activity) {
            $activity->properties = $activity->properties->put('agent', [
                'ip' => Request()->ip()
            ]);
        });
    }
}
