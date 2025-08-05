<?php

namespace App\Providers;

use App\Events\UserStatusChanged;
use App\Listeners\HandleUserStatusChanged;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar Sanctum para usar nuestro modelo personalizado
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Administrador') ? true : null;
        });

        // Registrar event listeners
        Event::listen(
            UserStatusChanged::class,
            HandleUserStatusChanged::class,
        );
    }
}
