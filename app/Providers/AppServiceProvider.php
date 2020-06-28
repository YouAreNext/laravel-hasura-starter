<?php

namespace App\Providers;

use App\Factories\AuthModelFactory;
use App\Contracts\AuthModelFactory as AuthModelFactoryContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AuthModelFactoryContract::class, AuthModelFactory::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
