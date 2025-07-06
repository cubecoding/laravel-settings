<?php

namespace Cubecoding\Settings;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Bind the SettingsManager to the container
        $this->app->singleton('cubecoding-settings', function ($app) {
            return new SettingsManager;
        });

        // Register the facade alias
        $this->app->alias('cubecoding-settings', SettingsManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Publish config and migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/settings.php' => config_path('settings.php'),
            ], 'cubecoding-settings-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'cubecoding-settings-migrations');
        }

        // Merge config file
        $this->mergeConfigFrom(__DIR__.'/../config/settings.php', 'settings');

        // Load migrations automatically if not published
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides()
    {
        return ['cubecoding-settings', SettingsManager::class];
    }
}
