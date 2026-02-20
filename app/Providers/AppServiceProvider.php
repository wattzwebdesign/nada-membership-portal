<?php

namespace App\Providers;

use App\Models\SiteSetting;
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

        try {
            $websiteId = SiteSetting::umamiWebsiteId();
            if ($websiteId) {
                config(['filament-umami-widgets.website_id' => $websiteId]);
            }
        } catch (\Throwable) {
            // Table may not exist yet during migrations
        }
    }
}
