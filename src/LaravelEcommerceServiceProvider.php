<?php

namespace Weble\LaravelEcommerce;

use Cknow\Money\Money;
use Illuminate\Support\ServiceProvider;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Currency\CurrencyManager;

class LaravelEcommerceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishResources();

        Money::setLocale($this->app->make('config')->get('ecommerce.locale'));
        Money::setCurrency($this->app->make('config')->get('ecommerce.currency'));
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'ecommerce');

        $this->registerCurrencyManager();
        $this->registerCartInstances();
        $this->registerCartManager();
    }

    protected function registerCurrencyManager()
    {
        $this->app->singleton('ecommerce.currencyManager', function ($app) {
            $class = $this->app['config']['ecommerce.classes.currencyManager'] ?? CurrencyManager::class;

            return new $class($app);
        });
    }

    protected function registerCartManager()
    {
        $this->app->singleton('ecommerce.cartManager', function ($app) {
            $class = $this->app['config']['ecommerce.classes.cartManager'] ?? CartManager::class;

            return new $class($app);
        });
    }

    protected function registerCartInstances()
    {
        $instances = array_keys($this->app['config']['ecommerce.cart_instances'] ?? []);
        foreach ($instances as $instance) {
            $this->app->singleton('ecommerce.cart.instance.' . $instance, function ($app) use ($instance) {
                return $app['ecommerce.cartManager']->instance($instance);
            });
        }
    }

    protected function publishResources(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('ecommerce.php'),
        ], 'config');

        if (! class_exists('CreateCartitemsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/0000_00_00_000000_create_cartitems_table.php' => database_path('migrations/'.$timestamp.'_create_cartitems_table.php'),
            ], 'migrations');
        }

        /*
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/skeleton'),
        ], 'views');
        */
    }
}
