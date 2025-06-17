<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') && app()->runningUnitTests()) {
            abort(500, 'Тесты запрещены в production!');
        }
        Paginator::useBootstrapFive(); // 👈 установит bootstrap-5 как дефолт
    }
}
