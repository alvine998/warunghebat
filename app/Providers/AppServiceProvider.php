<?php

namespace App\Providers;

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
        });

        View::composer(['legal.terms', 'legal.privacy'], function (ViewInstance $view): void {
            $view->with('officialEmail', Setting::officialEmail());
        });
    }
}
