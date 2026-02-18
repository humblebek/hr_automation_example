<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;
use App\Models\ApplicationOccupation;
use App\Observers\ApplicationOccupationObserver;

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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['uz','ru'])
                ->labels([
                    'uz' => "O'zbekcha",
                    'ru' => 'Ruscha',
                ]);
        });

        ApplicationOccupation::observe(ApplicationOccupationObserver::class);
    }
}
