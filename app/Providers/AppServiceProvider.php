<?php

namespace App\Providers;

use App\Auth\FileUserProvider;
use App\Support\LocalUserStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LocalUserStore::class, function ($app): LocalUserStore {
            return new LocalUserStore($app['config']->get('auth.providers.users.path'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('file', function ($app, array $config): FileUserProvider {
            return new FileUserProvider($app->make(LocalUserStore::class));
        });
    }
}
