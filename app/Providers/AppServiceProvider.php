<?php

namespace App\Providers;

use App\Models\Training;
use App\Observers\TrainingObserver;
use Illuminate\Support\ServiceProvider;

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
        Training::observe(TrainingObserver::class);
    }
}
