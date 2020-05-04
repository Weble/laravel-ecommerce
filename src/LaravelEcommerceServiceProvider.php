<?php

namespace Weble\LaravelEcommerce;

use Illuminate\Support\ServiceProvider;
use Weble\LaravelEcommerce\Cart\CartManager;

class LaravelEcommerceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishResources();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'ecommerce');

        $this->registerCartInstances();
        $this->registerCartManager();
    }

    protected function registerCartManager()
    {
        $this->app->singleton('ecommerce.cart', function ($app) {
            return new CartManager($app);
        });
    }

    protected function registerCartInstances()
    {
        $instances = array_keys($this->app['config']['ecommerce.cart_instances'] ?? []);
        foreach ($instances as $instance) {
            $this->app->singleton('ecommerce.cart.instance.' . $instance, function ($app) use ($instance) {
                return $app['ecommerce.cart']->instance($instance);
            });
        }
    }

    protected function publishResources(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('ecommerce.php'),
        ], 'config');

        /*
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/skeleton'),
        ], 'views');
        */
    }
}
