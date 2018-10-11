<?php

namespace App\Providers;

use App\Services\AccountScenario;
use App\Services\LoginSessionScenario;
use Illuminate\Support\ServiceProvider;
use App\Models\Domain\AccountRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Services\WeatherService');

        $this->app->bind(
            AccountRepository::class,
            \App\Infrastructure\Repositories\AccountRepository::class
        );

        $this->app->bind(
            AccountScenario::class,
            function () {
                return new AccountScenario(
                $this->app->make(AccountRepository::class)
            );
            }
        );

        $this->app->bind(
            LoginSessionScenario::class,
            function () {
                return new LoginSessionScenario(
                    $this->app->make(AccountRepository::class)
                );
            }
        );
    }
}
