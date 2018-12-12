<?php

namespace App\Providers;

use App\Services\StockScenario;
use App\Services\AccountScenario;
use App\Services\CategoryScenario;
use App\Services\LoginSessionScenario;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Domain\QiitaApiRepository;
use App\Models\Domain\Stock\StockRepository;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\Category\CategoryRepository;
use App\Models\Domain\LoginSession\LoginSessionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
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
            \App\Infrastructure\Repositories\Eloquent\AccountRepository::class
        );

        $this->app->bind(
            LoginSessionRepository::class,
            \App\Infrastructure\Repositories\Eloquent\LoginSessionRepository::class
        );

        $this->app->bind(
            CategoryRepository::class,
            \App\Infrastructure\Repositories\Eloquent\CategoryRepository::class
        );

        $this->app->bind(
            StockRepository::class,
            \App\Infrastructure\Repositories\Eloquent\StockRepository::class
        );

        $this->app->bind(
            QiitaApiRepository::class,
            \App\Infrastructure\Repositories\Api\QiitaApiRepository::class
        );

        $this->app->bind(
            AccountScenario::class,
            function () {
                return new AccountScenario(
                    $this->app->make(AccountRepository::class),
                    $this->app->make(LoginSessionRepository::class),
                    $this->app->make(CategoryRepository::class)
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

        $this->app->bind(
            CategoryScenario::class,
            function () {
                return new CategoryScenario(
                    $this->app->make(AccountRepository::class),
                    $this->app->make(LoginSessionRepository::class),
                    $this->app->make(CategoryRepository::class)
                );
            }
        );
        $this->app->bind(
            StockScenario::class,
            function () {
                return new StockScenario(
                    $this->app->make(AccountRepository::class),
                    $this->app->make(LoginSessionRepository::class),
                    $this->app->make(StockRepository::class),
                    $this->app->make(QiitaApiRepository::class)
                );
            }
        );
    }
}
