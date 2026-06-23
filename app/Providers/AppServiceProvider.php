<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        if ($this->app->runningUnitTests()) {
            return;
        }

        Livewire::setUpdateRoute(function ($handle) {
            $appPath = trim(parse_url(config('app.url'), PHP_URL_PATH) ?? '', '/');
            $updatePath = trim($appPath . '/livewire/update', '/');

            return Route::post($updatePath, $handle)
                ->middleware('web')
                ->name('livewire.update');
        });
    }
}
