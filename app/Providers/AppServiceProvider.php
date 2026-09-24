<?php

namespace App\Providers;

use App\Http\Controllers\PushSubscriptionController;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

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
        View::composer('layouts.app', function (ViewInstance $view): void {
            $view->with('socialLinks', Setting::socialLinks());
            // Public FCM web keys (null until the FIREBASE_* env keys are set).
            $view->with('firebaseConfig', PushSubscriptionController::publicConfig());
        });

        View::composer(['legal.terms', 'legal.privacy'], function (ViewInstance $view): void {
            $view->with('officialEmail', Setting::officialEmail());
        });
    }
}
