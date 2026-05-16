<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

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
        Model::shouldBeStrict(! app()->isProduction());

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Dynamic AI Configuration from Database (General Mode)
        try {
            if (class_exists(\App\Models\ChatbotSetting::class)) {
                $settings = \App\Models\ChatbotSetting::current();
                
                if ($settings->settings) {
                    foreach ($settings->settings as $provider => $values) {
                        if (isset($values['key'])) {
                            config(["ai.providers.{$provider}.key" => $values['key']]);
                        }
                        if (isset($values['url'])) {
                            config(["ai.providers.{$provider}.url" => $values['url']]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if DB is not ready
        }
    }
}
