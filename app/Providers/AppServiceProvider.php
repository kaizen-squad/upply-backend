<?php

namespace App\Providers;

use App\Enums\UserRole;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;
use App\Models\Review;
use App\Models\User;
use App\Observers\ReviewObserver;
use Illuminate\Support\Facades\Gate;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Review::observe(ReviewObserver::class);

        Gate::define('client-access-dashboard', function (User $user): bool {
            return $user->role === UserRole::Client;
        });

        Gate::define('prestataire-access-dashboard', function (User $user): bool{
            return $user->role !== UserRole::Prestataire;
        });
    }
}
