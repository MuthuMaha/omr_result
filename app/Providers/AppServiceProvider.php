<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
       date_default_timezone_set('Asia/Calcutta');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
    }
}
